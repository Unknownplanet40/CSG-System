// Purpose: To queue notifications to prevent multiple notifications from overlapping each other.

const notificationQueue = [];

export function QueueNotification([icon="info", message="Notification", duration=3000, position="bottom-end"]) {
    notificationQueue.push([icon, message, duration, position]);
    if (notificationQueue.length === 1) {
        ProcessNotificationQueue();
    }
}

function ShowNotification() {
    if (notificationQueue.length === 0) {
        return;
    }

    const [icon, message, duration, position] = notificationQueue[0];
    new Notification(message).onshow = () => {
        setTimeout(() => {
            CloseNotification();
        }, duration);
    }
    notificationQueue.shift();
    if (notificationQueue.length > 0) {
        ShowNotification();
    }
}

function CloseNotification() {
    if (notificationQueue.length > 0) {
        ProcessNotificationQueue();
    }
}

function ProcessNotificationQueue() {

    if (!("Notification" in window)) { // check if the browser supports notifications
        return;
    }

    if (Notification.permission === "granted") { // check if the user has granted permission to show notifications
        ShowNotification();
    }

    if (notificationQueue.length === 0) {
        return;
    }

    const [icon, message, duration, position] = notificationQueue[0];
    Swal.mixin({
        toast: true,
        position: position,
        showConfirmButton: false,
        timer: duration,
        timerProgressBar: true,
        customClass: {
            container: 'toast-container',
            popup: 'toast-popup',
            title: 'toast-title',
            icon: 'toast-icon',
        },
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    }).fire({
        icon: icon,
        title: message,
        didClose: () => {
            notificationQueue.shift();
            if (notificationQueue.length > 0) {
                ProcessNotificationQueue();
            }
        }
    });
}
