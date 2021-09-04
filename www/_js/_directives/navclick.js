app.directive('navClick', function($location) {
	return function(scope, element, attrs) {
		var path;

		attrs.$observe('navClick', function(val) {
			path = val;
		});

		element.bind('click', function() {
			scope.$apply(function() {
				$location.path(path);
			});
		});
	};
});
