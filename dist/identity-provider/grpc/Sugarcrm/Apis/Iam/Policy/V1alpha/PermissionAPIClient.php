<?php
// GENERATED CODE -- DO NOT EDIT!

// Original file comments:
// Copyright 2020 SugarCRM Inc. All rights reserved.
//
namespace Sugarcrm\Apis\Iam\Policy\V1alpha;

/**
 * Service for managing AuthZ Permissions
 */
class PermissionAPIClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \Sugarcrm\Apis\Iam\Policy\V1alpha\CreatePermissionRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function CreatePermission(\Sugarcrm\Apis\Iam\Policy\V1alpha\CreatePermissionRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/sugarcrm.apis.iam.policy.v1alpha.PermissionAPI/CreatePermission',
        $argument,
        ['\Sugarcrm\Apis\Iam\Resource\V1\Permission', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Sugarcrm\Apis\Iam\Policy\V1alpha\GetPermissionRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function GetPermission(\Sugarcrm\Apis\Iam\Policy\V1alpha\GetPermissionRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/sugarcrm.apis.iam.policy.v1alpha.PermissionAPI/GetPermission',
        $argument,
        ['\Sugarcrm\Apis\Iam\Resource\V1\Permission', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Sugarcrm\Apis\Iam\Policy\V1alpha\UpdatePermissionRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function UpdatePermission(\Sugarcrm\Apis\Iam\Policy\V1alpha\UpdatePermissionRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/sugarcrm.apis.iam.policy.v1alpha.PermissionAPI/UpdatePermission',
        $argument,
        ['\Sugarcrm\Apis\Iam\Resource\V1\Permission', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Sugarcrm\Apis\Iam\Policy\V1alpha\DeletePermissionRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function DeletePermission(\Sugarcrm\Apis\Iam\Policy\V1alpha\DeletePermissionRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/sugarcrm.apis.iam.policy.v1alpha.PermissionAPI/DeletePermission',
        $argument,
        ['\Google\Protobuf\GPBEmpty', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Sugarcrm\Apis\Iam\Policy\V1alpha\ListPermissionsRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function ListPermissions(\Sugarcrm\Apis\Iam\Policy\V1alpha\ListPermissionsRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/sugarcrm.apis.iam.policy.v1alpha.PermissionAPI/ListPermissions',
        $argument,
        ['\Sugarcrm\Apis\Iam\Policy\V1alpha\ListPermissionsResponse', 'decode'],
        $metadata, $options);
    }

}
