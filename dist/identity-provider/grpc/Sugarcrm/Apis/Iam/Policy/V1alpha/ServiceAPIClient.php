<?php
// GENERATED CODE -- DO NOT EDIT!

// Original file comments:
// Copyright 2020 SugarCRM Inc. All rights reserved.
//
namespace Sugarcrm\Apis\Iam\Policy\V1alpha;

/**
 * Service for managing AuthZ Services
 */
class ServiceAPIClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \Sugarcrm\Apis\Iam\Policy\V1alpha\CreateServiceRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function CreateService(\Sugarcrm\Apis\Iam\Policy\V1alpha\CreateServiceRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/sugarcrm.apis.iam.policy.v1alpha.ServiceAPI/CreateService',
        $argument,
        ['\Sugarcrm\Apis\Iam\Resource\V1\Service', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Sugarcrm\Apis\Iam\Policy\V1alpha\GetServiceRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function GetService(\Sugarcrm\Apis\Iam\Policy\V1alpha\GetServiceRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/sugarcrm.apis.iam.policy.v1alpha.ServiceAPI/GetService',
        $argument,
        ['\Sugarcrm\Apis\Iam\Resource\V1\Service', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Sugarcrm\Apis\Iam\Policy\V1alpha\UpdateServiceRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function UpdateService(\Sugarcrm\Apis\Iam\Policy\V1alpha\UpdateServiceRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/sugarcrm.apis.iam.policy.v1alpha.ServiceAPI/UpdateService',
        $argument,
        ['\Sugarcrm\Apis\Iam\Resource\V1\Service', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Sugarcrm\Apis\Iam\Policy\V1alpha\DeleteServiceRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function DeleteService(\Sugarcrm\Apis\Iam\Policy\V1alpha\DeleteServiceRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/sugarcrm.apis.iam.policy.v1alpha.ServiceAPI/DeleteService',
        $argument,
        ['\Google\Protobuf\GPBEmpty', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Sugarcrm\Apis\Iam\Policy\V1alpha\ListServicesRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function ListServices(\Sugarcrm\Apis\Iam\Policy\V1alpha\ListServicesRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/sugarcrm.apis.iam.policy.v1alpha.ServiceAPI/ListServices',
        $argument,
        ['\Sugarcrm\Apis\Iam\Policy\V1alpha\ListServicesResponse', 'decode'],
        $metadata, $options);
    }

}
