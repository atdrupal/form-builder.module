<li     class="draggable group field"
        ng-repeat="groupInfo in pageStack[pageUuid]|orderBy:'weight'"
        ng-if="groupInfo.isGroup">

    <div class="field-actions">
        <ul class="action-links">
            <li><a href ng-click="fieldConfig(pageUuid, groupInfo.uuid)">Config</a></li>
            <li><a href ng-click="fieldRemove(pageUuid, groupInfo.uuid)">Remove</a></li>
        </ul>
    </div>

    <strong class="group-title">{{groupInfo.title}} ({{groupInfo.type}})</strong>

    <div class="description">{{groupInfo.description}}</div>

    <div ng-if="groupInfo.editing">
        <div class="group-options" ng-if="'fieldset' === groupInfo.type">
            <div class="form-item form-type-checkbox form-item-collapsible">
                <input type="checkbox" id="edit-collapsible-{{groupInfo.uuid}}" class="form-checkbox" />
                <label class="option" for="edit-collapsible-{{groupInfo.uuid}}">Collapsible</label>
            </div>
            <div class="form-item form-type-checkbox form-item-collapsed">
                <input type="checkbox" id="edit-collapsed-{{groupInfo.uuid}}" class="form-checkbox" />
                <label class="option" for="edit-collapsed-{{groupInfo.uuid}}">Collapsed</label>
            </div>
        </div>
    </div>

    <!-- fields in group -->
    <ul>
        <!-- If there's no field added to page -->
        <li class="draggable empty"
            drop-channel="field"
            ui-on-Drop="fieldOnDrop($event, $data, '', pageUuid)"
            ng-show="!(groupInfo.fieldInfos | toArray).length">
            No field.
        </li>

        <li class="draggable field"
            ng-repeat="fieldInfo in groupInfo.fieldInfos|toArray"
            ng-init="fieldUuid = fieldInfo.ngKEY">
            {{entity.fields[fieldUuid].humanName}}
        </li>
    </ul>
</li>
