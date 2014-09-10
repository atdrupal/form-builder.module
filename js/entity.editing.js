(function (angular, $, Drupal) {

    angular.module('fob_entity_edit', ['ngDragDrop'])
            .controller('HelloCtrl', function ($http, $scope) {
                $scope.available = Drupal.settings.FormBuilder.available;
                $scope.available.addingFields = {};
                $scope.available.addedFields = {};
                $scope.entity = Drupal.settings.FormBuilder.entity;

                $scope.onDrop = function ($event, fieldName) {
                    $scope.available.addingFields[fieldName] = $scope.available.fields[fieldName];
                    delete($scope.available.fields[fieldName]);

                    $http
                            .post(window.location.pathname, {
                                action: 'add-field',
                                fieldName: fieldName,
                                entity: $scope.entity
                            })
                            .success(function (data) {
                                console.log(data);
                            });
                };

                $scope.submit = function () {
                    $http
                            .post(window.location.pathname, {
                                entity: $scope.entity
                            })
                            .success(function (data) {
                                console.log(data);
                            });
                };
            });

})(angular, jQuery, Drupal);
