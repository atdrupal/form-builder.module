(function (angular) {

    angular.module('FormBuilderGroupHelper', []).factory('$groupHelper', function ($http, $timeout) {
        var helper = {};

        helper.groupIsEmpty = function (pageId, groupId) {
            for (var fieldId in this.entity.layoutOptions.pages[pageId].fields)
                if (typeof this.entity.layoutOptions.pages[pageId].fields[fieldId].parent !== 'undefined')
                    if (groupId === this.entity.layoutOptions.pages[pageId].fields[fieldId].parent)
                        return false;
            return true;
        };

        helper.groupFixFieldWeight = function (pageId, groupId) {
            var arr = [];

            for (var fieldId in this.entity.layoutOptions.pages[pageId].fields)
                if (typeof this.entity.layoutOptions.pages[pageId].fields[fieldId].parent !== 'undefined')
                    if (groupId === this.entity.layoutOptions.pages[pageId].fields[fieldId].parent)
                        arr.push({id: fieldId, weight: this.entity.layoutOptions.pages[pageId].fields[fieldId].weight});

            arr.sort(function (a, b) {
                return a.weight - b.weight;
            });

            for (var i in arr) {
                var fieldId = arr[i].id;
                this.entity.layoutOptions.pages[pageId].fields[fieldId].weight = 2 * i;
            }
        };

        helper.groupDragValidate = function ($channel, $data) {
            return false;
        };

        /**
         * Cases to handle
         * 1. Drag form field to group
         * 2. Drag new field to group
         * 3. Drag group to group
         */
        helper.groupFieldOnDrop = function ($channel, $data, toPageId, groupId, toFieldId, increase) {
            this.groupFixFieldWeight(toPageId, groupId);

            switch ($channel) {
                case 'fieldInRoot':
                case 'fieldInGroup':
                    var fromFieldId = $data.itemInfo.uuid;
                    this.groupFieldOnDropField(toPageId, groupId, fromFieldId, toFieldId, increase);
                    break;
                case 'newField':
                    this.groupFieldOnDropNewField($data, toPageId, groupId, fromFieldId, toFieldId, increase);
            }
        };

        helper.groupFieldOnDropField = function (toPageId, groupId, fromFieldId, toFieldId, increase) {
            // @todo If user moves field from other page
            //  - add field to new page
            //  - remove field from old page
            if (typeof this.entity.layoutOptions.pages[toPageId].fields[fromFieldId] === 'undefined') {
                this.entity.layoutOptions.pages[toPageId].fields[fromFieldId] = {weight: 0, domTagName: 'div', domClasses: [], parent: null};
            }

            // Update field parent & weight
            this.entity.layoutOptions.pages[toPageId].fields[fromFieldId].parent = groupId;
            this.entity.layoutOptions.pages[toPageId].fields[fromFieldId].weight = 'undefined' === typeof this.entity.layoutOptions.pages[toPageId].fields[toFieldId]
                    ? 0
                    : increase + this.entity.layoutOptions.pages[toPageId].fields[toFieldId].weight;
        };

        helper.groupFieldOnDropNewField = function (fieldInfo, toPageId, groupId, fromFieldId, toFieldId, increase) {
            this.fieldOnDropAddField(this, toPageId, toFieldId, fieldInfo, increase);
        };

        // ---------------------
        // Stack item: config
        // ---------------------
        helper.stackItemConfig = function (pageId, itemId, isGroup) {
            for (var i in this.pageStack[pageId])
                if (itemId === this.pageStack[pageId][i].uuid)
                    this.pageStack[pageId][i].editing = !this.pageStack[pageId][i].editing;
        };

        helper.idGenerator = function () {
            function s4() {
                return Math.floor((1 + Math.random()) * 0x10000)
                        .toString(16)
                        .substring(1);
            }

            return s4() + s4() + '-' + s4() + '-' + s4() + '-' + s4() + '-' + s4() + s4() + s4();
        };

        // ---------------------
        // Add new group to a page
        // ---------------------
        helper.groupCreatenew = function (pageId) {
            var groupId = helper.idGenerator();
            this.entity.layoutOptions.pages[pageId].groups[groupId] = {
                "title": "New group",
                "type": "fieldset",
                "weight": 1000
            };
        };

        // ---------------------
        // Remove a group from page
        // ---------------------
        helper.stackItemRemove = function (pageId, itemId, isGroup) {
            if (!isGroup)
                return this.fieldRemove(pageId, itemId);

            var groupWeight = this.entity.layoutOptions.pages[pageId].groups[itemId].weight;

            // change parent for fields
            for (var fieldId in this.entity.layoutOptions.pages[pageId].fields)
                if (this.entity.layoutOptions.pages[pageId].fields[fieldId].parent !== 'undefined')
                    if (this.entity.layoutOptions.pages[pageId].fields[fieldId].parent === itemId) {
                        delete(this.entity.layoutOptions.pages[pageId].fields[fieldId].parent);
                        this.entity.layoutOptions.pages[pageId].fields[fieldId].weight = groupWeight;
                    }

            // Remove group from page
            delete(this.entity.layoutOptions.pages[pageId].groups[itemId]);
        };

        return helper;
    });

})(angular);
