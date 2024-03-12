let workerIntervalId = null;
self.onmessage = function (e) {
  console.log('[worker] received message', e.data);
  let {
    refreshActive,
    missionId,
    refreshIntervalMs
  } = e.data;
  if (refreshActive && workerIntervalId == null) {
    startRefresh(missionId, refreshIntervalMs);
  }
  if (!refreshActive && workerIntervalId !== null) {
    stopRefresh();
  }
};
function startRefresh(missionId, refreshIntervalMs) {
  console.log('specialMissionWorker::startRefresh');
  workerIntervalId = setInterval(() => fetchMissionData(missionId), refreshIntervalMs);
}
function stopRefresh() {
  console.log('specialMissionWorker::stopRefresh');
  if (workerIntervalId == null) return;
  clearInterval(workerIntervalId);
  workerIntervalId = null;
}
function fetchMissionData(missionId) {
  const apiUrl = `/api/legacy_special_missions.php?mission_id=${missionId}`;
  fetch(apiUrl).then(response => response.json()).then(data => postMessage(data));
}