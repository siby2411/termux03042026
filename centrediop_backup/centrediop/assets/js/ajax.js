// Fonction AJAX générique
function sendAjaxRequest(url, method, data, successCallback) {
    const xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            successCallback(xhr.responseText);
        }
    };
    xhr.send(data);
}

// Rafraîchir la file d'attente
function refreshQueue(serviceId) {
    sendAjaxRequest(
        'modules/queue/list_queue.php?service_id=' + serviceId,
        'GET',
        null,
        function(response) {
            document.getElementById('queue-list').innerHTML = response;
        }
    );
}

// Rafraîchir toutes les 30 secondes
setInterval(() => refreshQueue(1), 30000);
