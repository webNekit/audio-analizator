<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Анализ аудиофайла
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded shadow overflow-hidden">
                <h3 class="text-2xl font-semibold mb-4">{{ $audioFile->file_name }}</h3>

                <audio id="audio" controls class="w-full mb-6">
                    <source src="{{ asset('storage/' . $audioFile->file_path) }}" type="audio/mpeg">
                    Ваш браузер не поддерживает воспроизведение аудио.
                </audio>

                <!-- Результат анализа -->
                <div id="analysis" class="bg-gray-100 p-4 rounded mb-8 text-gray-700">
                    <h4 class="font-semibold text-lg mb-2">Результат анализа:</h4>
                    <p>Нажмите "Воспроизвести" для начала анализа...</p>
                </div>

                <div class="my-6">
                    <button onclick="downloadReport()" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition">
                        Скачать отчет
                    </button>
                </div>

                <!-- Графики -->
                <div class="space-y-8">
                    <div class="bg-gray-50 p-4 rounded shadow overflow-hidden">
                        <h5 class="font-medium text-gray-800 mb-1">RMS (Средняя громкость)</h5>
                        <p class="text-gray-500 text-sm mb-2">Отображает уровень громкости аудиофайла во времени. Низкие значения — тихо, высокие — громко.</p>
                        <div class="overflow-x-auto">
                            <canvas id="rmsChart" class="h-64"></canvas>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded shadow overflow-hidden">
                        <h5 class="font-medium text-gray-800 mb-1">ZCR (Частота пересечения нуля)</h5>
                        <p class="text-gray-500 text-sm mb-2">Показывает наличие шумов и резких звуков. Чем выше — тем больше шумов и шипящих звуков.</p>
                        <div class="overflow-x-auto">
                            <canvas id="zcrChart" class="h-64"></canvas>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded shadow overflow-hidden">
                        <h5 class="font-medium text-gray-800 mb-1">Spectral Centroid (Центроид спектра)</h5>
                        <p class="text-gray-500 text-sm mb-2">Отображает среднюю «высоту» звука. Низкие значения — басовитость, высокие — звонкость.</p>
                        <div class="overflow-x-auto">
                            <canvas id="centroidChart" class="h-64"></canvas>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded shadow overflow-hidden">
                        <h5 class="font-medium text-gray-800 mb-1">MFCC (Эквалайзер)</h5>
                        <p class="text-gray-500 text-sm mb-2">Показывает частотный спектр записи. Используется для оценки чистоты и ясности звучания.</p>
                        <div class="overflow-x-auto">
                            <canvas id="mfccChart" class="h-64"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Meyda и Chart.js -->
    <script src="https://unpkg.com/meyda/dist/web/meyda.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const audioElement = document.getElementById('audio');
        const analysisDiv = document.getElementById('analysis');

        let audioContext, source, analyzer, meydaAnalyzer;
        let frame = 0;

        const createLineChart = (ctx, label, color) => new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label,
                    data: [],
                    borderColor: color,
                    fill: false,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                animation: false,
                maintainAspectRatio: false,
                scales: {
                    x: { display: false },
                    y: { beginAtZero: true }
                }
            }
        });

        const rmsChart = createLineChart(document.getElementById('rmsChart').getContext('2d'), 'RMS', 'rgb(75,192,192)');
        const zcrChart = createLineChart(document.getElementById('zcrChart').getContext('2d'), 'ZCR', 'rgb(192,75,192)');
        const centroidChart = createLineChart(document.getElementById('centroidChart').getContext('2d'), 'Centroid (Hz)', 'rgb(192,192,75)');

        function downloadReport() {
            const analysisContent = analysisDiv.innerText;

            const blob = new Blob([analysisContent], { type: 'text/plain;charset=utf-8' });
            const url = URL.createObjectURL(blob);

            const link = document.createElement('a');
            link.href = url;
            link.download = `Анализ_${{{ $audioFile->id }}}.txt`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            URL.revokeObjectURL(url);
        }

        const mfccChart = new Chart(document.getElementById('mfccChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: Array.from({length: 13}, (_, i) => `MFCC ${i+1}`),
                datasets: [{
                    label: 'MFCC Coefficients',
                    data: Array(13).fill(0),
                    backgroundColor: 'rgba(54, 162, 235, 0.5)'
                }]
            },
            options: {
                responsive: true,
                animation: false,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        audioElement.addEventListener('play', () => {
            if (!audioContext) {
                audioContext = new (window.AudioContext || window.webkitAudioContext)();
                source = audioContext.createMediaElementSource(audioElement);
                analyzer = audioContext.createAnalyser();
                source.connect(analyzer);
                analyzer.connect(audioContext.destination);

                meydaAnalyzer = Meyda.createMeydaAnalyzer({
                    audioContext: audioContext,
                    source: source,
                    bufferSize: 512,
                    featureExtractors: ['rms', 'zcr', 'spectralCentroid', 'mfcc'],
                    callback: features => {
                        let recommendations = [];

                        if (features.spectralCentroid < 300) recommendations.push('Тональность записи слишком низкая. Постарайтесь повысить интонацию.');
                        if (features.spectralCentroid > 3000) recommendations.push('Запись кажется чересчур звонкой. Попробуйте снизить тон.');
                        if (features.zcr > 0.2) recommendations.push('Много шумов или шипящих. Попробуйте использовать поп-фильтр или записывать в более тихом месте.');
                        if (features.rms < 0.005) recommendations.push('Громкость слишком низкая. Записывайте ближе к микрофону.');
                        if (features.rms > 0.05) recommendations.push('Громкость слишком высокая. Попробуйте отойти от микрофона.');
                        if (Math.abs(features.mfcc[0]) > 300) recommendations.push('Есть признаки искажений частот. Постарайтесь записывать в более чистой акустической обстановке.');

                        if (recommendations.length === 0) recommendations.push('Запись звучит хорошо! Продолжайте работать в том же духе.');

                        analysisDiv.innerHTML = `
                            <h4 class="font-semibold text-lg mb-2">Результат анализа:</h4>
                            <p><strong>RMS (Средняя громкость):</strong> ${features.rms.toFixed(4)}</p>
                            <p><strong>ZCR (Частота пересечения нуля):</strong> ${features.zcr.toFixed(4)}</p>
                            <p><strong>Centroid (Центроид спектра):</strong> ${features.spectralCentroid.toFixed(2)} Hz</p>
                            <div class="mt-4 bg-yellow-100 text-yellow-800 p-3 rounded shadow space-y-2">
                                ${recommendations.map(rec => `<p>• ${rec}</p>`).join('')}
                            </div>
                        `;

                        // Обновляем графики
                        [rmsChart, zcrChart, centroidChart].forEach(chart => {
                            chart.data.labels.push(frame++);
                            if (chart.data.labels.length > 200) {
                                chart.data.labels.shift();
                                chart.data.datasets[0].data.shift();
                            }
                        });

                        rmsChart.data.datasets[0].data.push(features.rms);
                        zcrChart.data.datasets[0].data.push(features.zcr);
                        centroidChart.data.datasets[0].data.push(features.spectralCentroid);

                        rmsChart.update();
                        zcrChart.update();
                        centroidChart.update();

                        mfccChart.data.datasets[0].data = features.mfcc;
                        mfccChart.update();
                    }
                });

                meydaAnalyzer.start();
            }
        });

        audioElement.addEventListener('pause', () => {
            if (meydaAnalyzer) meydaAnalyzer.stop();
        });
    </script>
</x-app-layout>
