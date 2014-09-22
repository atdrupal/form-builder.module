(function (angular, Drupal) {
    var module = angular.module('FormBuilderApp', ['ngDragDrop', 'FormBuilderPageHelper', 'FormBuilderFieldHelper', 'FormBuilderEntityTypeHelper', 'FormBuilderFormHelper']);

    module.factory('$initState', function () {
        var initState = {
            available: Drupal.settings.FormBuilder.available,
            entity: Drupal.settings.FormBuilder.entity,
            newPageTitle: '',
            newPageAdding: false
        };

        angular.extend(initState.available, {
            addingEntityTypeNames: {},
            addingFields: {},
            addedFields: {}
        });

        if (initState.entity.layoutOptions.pages instanceof Array)
            initState.entity.layoutOptions.pages = {};

        for (var pageUuid in initState.entity.layoutOptions.pages)
            if (initState.entity.layoutOptions.pages[pageUuid].fields instanceof Array)
                initState.entity.layoutOptions.pages[pageUuid].fields = {};

        return initState;
    });

    module.factory('$helpers', function ($initState, $pageHelper, $fieldHelper, $entityTypeHelper, $formHelper) {
        var helpers = $initState;

        angular.extend(helpers, $pageHelper);
        angular.extend(helpers, $fieldHelper);
        angular.extend(helpers, $entityTypeHelper);
        angular.extend(helpers, $formHelper);

        return helpers;
    });

    module.controller('FormBuilderForm', function ($scope, $helpers) {
        angular.extend($scope, $helpers);

        // if empty, $scope.entity.fields is array!
        if ($scope.entity.fields instanceof Array)
            $scope.entity.fields = {};

        // Layout options -> fields
        $scope.pageFields = {};
        $scope.$watch('entity.layoutOptions.pages', function (pages) {
            $scope.pageFields = {};
            angular.forEach(pages, function (pageInfo, pageUuid) {
                $scope.pageFields[pageUuid] = [];
                angular.forEach(pageInfo.fields, function (fieldInfo, fieldUuid) {
                    fieldInfo.uuid = fieldUuid;
                    $scope.pageFields[pageUuid].push(fieldInfo);
                });
            });
        }, true);
    });

})(angular, Drupal);
