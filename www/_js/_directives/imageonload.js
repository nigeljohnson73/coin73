// Adds the event handler onload to images:
//	<img class="img-responsive visible-xs" src="/gfx/logo-200.png" alt="logo" onload="loadedLogo(200)" />

app.directive('imageonload', [ function() {
	return {
	restrict : 'A',
	link : function(scope, element, attrs) {
		element.bind('load', function() {
			//call the function that was passed
			scope.$apply(attrs.imageonload);
		});
	}
	};
} ]);
