<?php
// GENERATED CODE -- DO NOT EDIT!

// Original file comments:
// Copyright 2020 SugarCRM Inc. All rights reserved.
//
namespace Sugarcrm\Apis\Iam\Policy\V1alpha;

/**
 * Service for managing AuthZ Scopes
 */
class ScopeAPIClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \Sugarcrm\Apis\Iam\Policy\V1alpha\CreateScopeRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function CreateScope(\Sugarcrm\Apis\Iam\Policy\V1alpha\CreateScopeRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/sugarcrm.apis.iam.policy.v1alpha.ScopeAPI/CreateScope',
        $argument,
        ['\Sugarcrm\Apis\Iam\Resource\V1\Scope', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Sugarcrm\Apis\Iam\Policy\V1alpha\GetScopeRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function GetScope(\Sugarcrm\Apis\Iam\Policy\V1alpha\GetScopeRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/sugarcrm.apis.iam.policy.v1alpha.ScopeAPI/GetScope',
        $argument,
        ['\Sugarcrm\Apis\Iam\Resource\V1\Scope', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Sugarcrm\Apis\Iam\Policy\V1alpha\UpdateScopeRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function UpdateScope(\Sugarcrm\Apis\Iam\Policy\V1alpha\UpdateScopeRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/sugarcrm.apis.iam.policy.v1alpha.ScopeAPI/UpdateScope',
        $argument,
        ['\Sugarcrm\Apis\Iam\Resource\V1\Scope', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Sugarcrm\Apis\Iam\Policy\V1alpha\DeleteScopeRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function DeleteScope(\Sugarcrm\Apis\Iam\Policy\V1alpha\DeleteScopeRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/sugarcrm.apis.iam.policy.v1alpha.ScopeAPI/DeleteScope',
        $argument,
        ['\Google\Protobuf\GPBEmpty', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Sugarcrm\Apis\Iam\Policy\V1alpha\ListScopesRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function ListScopes(\Sugarcrm\Apis\Iam\Policy\V1alpha\ListScopesRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/sugarcrm.apis.iam.policy.v1alpha.ScopeAPI/ListScopes',
        $argument,
        ['\Sugarcrm\Apis\Iam\Policy\V1alpha\ListScopesResponse', 'decode'],
        $metadata, $options);
    }

}
