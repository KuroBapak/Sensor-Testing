# Gotcha: router.reload() opsi default sudah preserveState + preserveScroll
`router.reload(options)` di Inertia v3 SECARA DEFAULT sudah set
preserveState=true DAN preserveScroll=true. Jangan tulis keduanya
eksplisit — akan gagal type-check (properti tidak valid di versi ini).

Cukup: router.reload({ only: ['tanks'] })