		importScripts('https://www.gstatic.com/firebasejs/3.6.8/firebase-app.js');
		importScripts('https://www.gstatic.com/firebasejs/3.6.8/firebase-messaging.js');

		firebase.initializeApp({
    		messagingSenderId: '<624075254839>'
		});

		const messaging = firebase.messaging();