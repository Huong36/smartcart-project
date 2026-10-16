/**
 * SmartCart - Weather Widget for Ho Chi Minh City using Open-Meteo API
 * Phụ trách: Thành viên 4 (nongvanminh)
 */

(function () {
    'use strict';

    // Bảng mã thời tiết WMO (World Meteorological Organization) dịch sang tiếng Việt
    const WMO_WEATHER_MAP = {
        0: { text: 'Trời quang đãng', icon: '☀️' },
        1: { text: 'Ít mây', icon: '🌤️' },
        2: { text: 'Mây rải rác', icon: '⛅' },
        3: { text: 'Nhiều mây u ám', icon: '☁️' },
        45: { text: 'Có sương mù', icon: '🌫️' },
        48: { text: 'Sương mù đọng sương', icon: '🌫️' },
        51: { text: 'Mưa phùn nhẹ', icon: '🌦️' },
        53: { text: 'Mưa phùn vừa', icon: '🌦️' },
        55: { text: 'Mưa phùn dày hạt', icon: '🌧️' },
        61: { text: 'Mưa nhỏ rải rác', icon: '🌧️' },
        63: { text: 'Mưa vừa', icon: '🌧️' },
        65: { text: 'Mưa to', icon: '🌧️' },
        80: { text: 'Mưa rào nhẹ', icon: '🌦️' },
        81: { text: 'Mưa rào vừa', icon: '🌧️' },
        82: { text: 'Mưa rào dữ dội', icon: '⛈️' },
        95: { text: 'Có dông sét', icon: '⛈️' },
        96: { text: 'Dông kèm mưa đá nhỏ', icon: '⛈️' },
        99: { text: 'Dông bão mạnh kèm mưa đá', icon: '⛈️' }
    };

    function getWeatherInfo(code) {
        return WMO_WEATHER_MAP[code] || { text: 'Thời tiết ổn định', icon: '🌡️' };
    }

    async function loadHcmWeather() {
        const widgetContainer = document.getElementById('hcm-weather-widget');
        if (!widgetContainer) return;

        // Vị trí TP. Hồ Chí Minh
        const latitude = 10.8231;
        const longitude = 106.6297;
        const apiUrl = `https://api.open-meteo.com/v1/forecast?latitude=${latitude}&longitude=${longitude}&current=temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m&timezone=Asia%2FHo_Chi_Minh`;

        // Timeout 6 giây bằng AbortController
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 6000);

        try {
            const response = await fetch(apiUrl, {
                signal: controller.signal,
                headers: { 'Accept': 'application/json' }
            });

            clearTimeout(timeoutId);

            if (!response.ok) {
                throw new Error(`Open-Meteo HTTP status ${response.status}`);
            }

            const data = await response.json();

            if (!data || !data.current) {
                throw new Error('Dữ liệu thời tiết trả về không hợp lệ');
            }

            const current = data.current;
            const temp = Math.round(current.temperature_2m);
            const humidity = current.relative_humidity_2m;
            const wind = current.wind_speed_10m;
            const weather = getWeatherInfo(current.weather_code);

            // Định dạng thời gian cập nhật
            let updateTimeStr = '';
            try {
                const dateObj = new Date(current.time);
                updateTimeStr = dateObj.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' }) + ' ' + dateObj.toLocaleDateString('vi-VN');
            } catch (e) {
                updateTimeStr = current.time;
            }

            // Render widget thời tiết sạch sẽ, không dùng dữ liệu giả
            widgetContainer.innerHTML = `
                <div class="d-flex align-items-center gap-3">
                    <div style="font-size: 2rem; line-height: 1;">${weather.icon}</div>
                    <div>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="fw-bold text-dark fs-5">${temp}°C</span>
                            <span class="text-secondary small">TP. Hồ Chí Minh</span>
                            <span class="badge bg-light text-dark border small">${weather.text}</span>
                        </div>
                        <div class="text-muted" style="font-size: 0.75rem;">
                            Độ ẩm: ${humidity}% · Gió: ${wind} km/h · Cập nhật: ${updateTimeStr}
                            · <a href="https://open-meteo.com/" target="_blank" rel="noopener noreferrer" class="text-secondary text-decoration-underline">Nguồn: Open-Meteo</a>
                        </div>
                    </div>
                </div>
            `;
        } catch (err) {
            clearTimeout(timeoutId);
            console.warn('Weather Widget notice:', err.message || err);

            // Báo lỗi rõ ràng và lịch sự, TUYỆT ĐỐI không chèn số liệu fake
            widgetContainer.innerHTML = `
                <div class="text-muted small d-flex align-items-center gap-2">
                    <span>⛅ TP. Hồ Chí Minh:</span>
                    <span class="text-secondary">Không thể kết nối dịch vụ thời tiết lúc này.</span>
                    <span style="font-size: 0.7rem;">(Nguồn: Open-Meteo)</span>
                </div>
            `;
        }
    }

    document.addEventListener('DOMContentLoaded', loadHcmWeather);
})();
