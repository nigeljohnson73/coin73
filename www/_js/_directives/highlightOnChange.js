app.directive('highlightOnChange', function ($timeout) {
	return {
		link: function ($scope, element, attrs) {
			attrs.$observe('highlightOnChange', function (val) {
				element.addClass('flash');
				$timeout(function () {
					element.removeClass('flash');
				}, 2000);
			});
		}
	};
});
