/*
			_   ____
._ __   ___| |_/ ___|_   _____
| '_ \ / _ \ __\___ \ \ / / __|
| | | |  __/ |_ ___) \ V / (__
|_| |_|\___|\__|____/ \_/ \___|

*/
app.service('netSvc', [function () {
	netSvc = this;
	netSvc.state = false;
	netSvc.stateListener = [];
	netSvc.hadListener = false;

	/***
	* Allows a client to be informed when the system detects a network change
	*/
	netSvc.addStateListener = function (cb) {
		logger("netSvc.addStateListener()", "dbg");
		netSvc.stateListener.push(cb);
		if (netSvc.hadListener) {
			logger("netSvc.addStateListener() - already had a change registered", "dbg");
			cb(netSvc.state, true); // call back with the current state
		}
	};

	/***
	* The callback used by the system internals that will notify all the clients
	*/
	netSvc._updateOnlineStatus = function (event) {
		netSvc.state = navigator.onLine;

		logger("netSvc.updateOnlineStatus(" + netSvc.state + ")");
		var i;
		for (i = 0; i < netSvc.stateListener.length; i++) {
			logger("netSvc.updateOnlineStatus(): calling event listener");
			netSvc.stateListener[i](netSvc.state);
		}
		netSvc.hadListener = true;
	};

	logger("netSvc.initialisingListeners()", "dbg");
	window.addEventListener('online', netSvc._updateOnlineStatus);
	window.addEventListener('offline', netSvc._updateOnlineStatus);
	netSvc._updateOnlineStatus();

}]);

