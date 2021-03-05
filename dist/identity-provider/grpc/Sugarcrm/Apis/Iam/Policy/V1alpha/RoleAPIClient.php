<?php
// GENERATED CODE -- DO NOT EDIT!

// Original file comments:
// Copyright 2020 SugarCRM Inc. All rights reserved.
//
namespace Sugarcrm\Apis\Iam\Policy\V1alpha;

/**
 * Service for managing AuthZ Roles
 */
class RoleAPIClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \Sugarcrm\Apis\Iam\Policy\V1alpha\CreateRoleRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function CreateRole(\Sugarcrm\Apis\Iam\Policy\V1alpha\CreateRoleRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/sugarcrm.apis.iam.policy.v1alpha.RoleAPI/CreateRole',
        $argument,
        ['\Sugarcrm\Apis\Iam\Resource\V1\Role', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Sugarcrm\Apis\Iam\Policy\V1alpha\UpdateRoleRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function UpdateRole(\Sugarcrm\Apis\Iam\Policy\V1alpha\UpdateRoleRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/sugarcrm.apis.iam.policy.v1alpha.RoleAPI/UpdateRole',
        $argument,
        ['\Sugarcrm\Apis\Iam\Resource\V1\Role', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Sugarcrm\Apis\Iam\Policy\V1alpha\DeleteRoleRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function DeleteRole(\Sugarcrm\Apis\Iam\Policy\V1alpha\DeleteRoleRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/sugarcrm.apis.iam.policy.v1alpha.RoleAPI/DeleteRole',
        $argument,
        ['\Google\Protobuf\GPBEmpty', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Sugarcrm\Apis\Iam\Policy\V1alpha\GetRoleRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function GetRole(\Sugarcrm\Apis\Iam\Policy\V1alpha\GetRoleRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/sugarcrm.apis.iam.policy.v1alpha.RoleAPI/GetRole',
        $argument,
        ['\Sugarcrm\Apis\Iam\Resource\V1\Role', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Sugarcrm\Apis\Iam\Policy\V1alpha\ListRolesRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function ListRoles(\Sugarcrm\Apis\Iam\Policy\V1alpha\ListRolesRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/sugarcrm.apis.iam.policy.v1alpha.RoleAPI/ListRoles',
        $argument,
        ['\Sugarcrm\Apis\Iam\Policy\V1alpha\ListRolesResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Sugarcrm\Apis\Iam\Policy\V1alpha\BindRoleRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function BindRole(\Sugarcrm\Apis\Iam\Policy\V1alpha\BindRoleRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/sugarcrm.apis.iam.policy.v1alpha.RoleAPI/BindRole',
        $argument,
        ['\Sugarcrm\Apis\Iam\Policy\V1alpha\BindRoleResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Sugarcrm\Apis\Iam\Policy\V1alpha\UnbindRoleRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function UnbindRole(\Sugarcrm\Apis\Iam\Policy\V1alpha\UnbindRoleRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/sugarcrm.apis.iam.policy.v1alpha.RoleAPI/UnbindRole',
        $argument,
        ['\Sugarcrm\Apis\Iam\Policy\V1alpha\UnbindRoleResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Sugarcrm\Apis\Iam\Policy\V1alpha\GetRolesForSubjectRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function GetRolesForSubject(\Sugarcrm\Apis\Iam\Policy\V1alpha\GetRolesForSubjectRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/sugarcrm.apis.iam.policy.v1alpha.RoleAPI/GetRolesForSubject',
        $argument,
        ['\Sugarcrm\Apis\Iam\Policy\V1alpha\GetRolesForSubjectResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Sugarcrm\Apis\Iam\Policy\V1alpha\ListRoleSubjectsRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function ListRoleSubjects(\Sugarcrm\Apis\Iam\Policy\V1alpha\ListRoleSubjectsRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/sugarcrm.apis.iam.policy.v1alpha.RoleAPI/ListRoleSubjects',
        $argument,
        ['\Sugarcrm\Apis\Iam\Policy\V1alpha\ListRoleSubjectsResponse', 'decode'],
        $metadata, $options);
    }

}
