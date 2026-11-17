// Konfigurasi Resmi Firebase Cloud Firestore PKBM Mandiri
// Sinkronisasi Real-Time PPDB & Nilai Ujian Antar Perangkat (HP Calon Siswa <-> Laptop Admin)

const firebaseConfig = {
    apiKey: "AIzaSyABbn3IklnomOw8wexSxFYwjC2-DBmeqQI",
    authDomain: "pkbm-mandiri.firebaseapp.com",
    projectId: "pkbm-mandiri",
    storageBucket: "pkbm-mandiri.firebasestorage.app",
    messagingSenderId: "30362633045",
    appId: "1:30362633045:web:8c5319dc529404e13725dc",
    measurementId: "G-2Q2PVMFRQE"
};

let db = null;

try {
    if (typeof firebase !== 'undefined') {
        if (!firebase.apps.length) {
            firebase.initializeApp(firebaseConfig);
        }
        db = firebase.firestore();
        console.log("✅ Firebase Cloud Firestore terhubung ke proyek pkbm-mandiri");
    }
} catch (e) {
    console.warn("⚠️ Firebase Firestore offline atau gagal inisialisasi:", e);
}

// Helper PPDB: Simpan Calon Siswa ke Cloud Firestore & LocalStorage
window.simpanPendaftarOnline = async function(data) {
    // Selalu simpan ke local cache untuk fallback offline
    try {
        let localList = JSON.parse(localStorage.getItem('pkbm_pendaftar') || '[]');
        localList = localList.filter(item => item.no !== data.no);
        localList.unshift(data);
        localStorage.setItem('pkbm_pendaftar', JSON.stringify(localList));
    } catch(err) {
        console.warn("LocalStorage save error:", err);
    }

    // Simpan ke Cloud Firestore untuk sinkronisasi antar perangkat
    if (db) {
        try {
            await db.collection('pendaftar').doc(data.no).set({
                ...data,
                createdAt: firebase.firestore.FieldValue.serverTimestamp()
            }, { merge: true });
            console.log("✅ Data PPDB berhasil disinkronkan ke Cloud Firestore:", data.no);
            return { success: true, mode: 'cloud' };
        } catch (error) {
            console.error("Gagal simpan ke Firestore, menggunakan mode offline:", error);
            return { success: true, mode: 'local', error };
        }
    }
    return { success: true, mode: 'local' };
};

// Helper PPDB: Berlangganan Data Pendaftar Real-Time untuk Admin
window.listenPendaftarRealtime = function(onDataUpdated) {
    if (db) {
        return db.collection('pendaftar')
            .onSnapshot((snapshot) => {
                const cloudList = [];
                snapshot.forEach(doc => {
                    const item = doc.data();
                    cloudList.push({ id: doc.id, ...item });
                });
                // Sort by date / timestamp descending
                cloudList.sort((a, b) => {
                    const tA = a.createdAt ? (a.createdAt.seconds || 0) : 0;
                    const tB = b.createdAt ? (b.createdAt.seconds || 0) : 0;
                    return tB - tA;
                });
                // Update local storage backup
                if (cloudList.length > 0) {
                    localStorage.setItem('pkbm_pendaftar', JSON.stringify(cloudList));
                }
                onDataUpdated(cloudList, 'cloud');
            }, (error) => {
                console.warn("Firestore snapshot error, fallback ke localStorage:", error);
                const localList = JSON.parse(localStorage.getItem('pkbm_pendaftar') || '[]');
                onDataUpdated(localList, 'local');
            });
    } else {
        const localList = JSON.parse(localStorage.getItem('pkbm_pendaftar') || '[]');
        onDataUpdated(localList, 'local');
        return null;
    }
};

// Helper PPDB: Hapus / Tolak Pendaftar
window.hapusPendaftarCloud = async function(id) {
    // Hapus dari localStorage
    try {
        let localList = JSON.parse(localStorage.getItem('pkbm_pendaftar') || '[]');
        localList = localList.filter(item => (item.id !== id && item.no !== id));
        localStorage.setItem('pkbm_pendaftar', JSON.stringify(localList));
    } catch(err) {}

    // Hapus dari Firestore
    if (db) {
        try {
            await db.collection('pendaftar').doc(id).delete();
            console.log("✅ Pendaftar dihapus dari cloud:", id);
        } catch(e) {
            console.error("Gagal hapus dari cloud:", e);
        }
    }
};

// Helper PPDB: Terima Pendaftar
window.terimaPendaftarCloud = async function(id) {
    // Hapus dari pendaftar aktif di Firestore
    if (db) {
        try {
            await db.collection('pendaftar').doc(id).delete();
        } catch(e) {
            console.error(e);
        }
    }
};

// Helper Ujian CBT: Simpan Nilai Ujian ke Cloud
window.simpanNilaiUjianCloud = async function(scoreRecord) {
    if (db) {
        try {
            const docId = `${scoreRecord.nik}_${scoreRecord.exam_id || 'exam'}_${Date.now()}`;
            await db.collection('nilai_ujian').doc(docId).set({
                ...scoreRecord,
                createdAt: firebase.firestore.FieldValue.serverTimestamp()
            });
            console.log("✅ Nilai CBT tersimpan di Cloud:", docId);
        } catch(e) {
            console.warn("Gagal simpan nilai ke Cloud:", e);
        }
    }
};

// Helper Ujian CBT: Dengarkan Nilai Masuk Secara Real-Time
window.listenNilaiUjianRealtime = function(onDataUpdated) {
    if (db) {
        return db.collection('nilai_ujian')
            .onSnapshot((snapshot) => {
                const list = [];
                snapshot.forEach(doc => {
                    list.push({ id: doc.id, ...doc.data() });
                });
                list.sort((a, b) => {
                    const tA = a.createdAt ? (a.createdAt.seconds || 0) : 0;
                    const tB = b.createdAt ? (b.createdAt.seconds || 0) : 0;
                    return tB - tA;
                });
                onDataUpdated(list, 'cloud');
            }, (err) => {
                const local = JSON.parse(localStorage.getItem('pkbm_master_scores') || '[]');
                onDataUpdated(local, 'local');
            });
    } else {
        const local = JSON.parse(localStorage.getItem('pkbm_master_scores') || '[]');
        onDataUpdated(local, 'local');
        return null;
    }
};

