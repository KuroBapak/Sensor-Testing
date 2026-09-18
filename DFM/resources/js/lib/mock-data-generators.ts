export const generateMainTankData = () => {
    const hour = new Date().getHours();
    return {
        waktu: `${hour}:00`,
        total_liter: Math.floor(Math.random() * 5000) + 15000,
        liter_masuk: Math.floor(Math.random() * 1000) + 500,
        liter_keluar: Math.floor(Math.random() * 800) + 300,
    };
};

export const generateMobileTankData = (tankType: 'fuel_tanker' | 'browser') => {
    return {
        rfid: `${Math.random().toString(16).substring(2, 7).toUpperCase()}(Sector ${Math.floor(Math.random() * 10) + 1})`,
        waktu: new Date().toLocaleTimeString('id-ID'),
        liter: Math.floor(Math.random() * 500) + 100,
        tank_type: tankType,
    };
};

export const generateAlarmData = () => {
    return {
        rfid: `${Math.random().toString(16).substring(2, 7).toUpperCase()}(Sector ${Math.floor(Math.random() * 10) + 1})`,
        waktu_kejadian: new Date().toLocaleString('id-ID'),
        jumlah_liter: Math.floor(Math.random() * 300) + 50,
    };
};
