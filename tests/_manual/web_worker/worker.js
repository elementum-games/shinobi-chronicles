onmessage = function(e) {
    console.log('[worker] starting count', e);

    let workerCount = 0;
    const workerIntervalId = setInterval(incrementWorkerCount, 800);
    function incrementWorkerCount() {
        if(workerCount >= 1000) {
            clearInterval((workerIntervalId));
            return;
        }

        workerCount++;
        postMessage({ newCount: workerCount });
    }
}