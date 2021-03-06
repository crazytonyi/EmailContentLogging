<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: apis/iam/policy/v1alpha/permission.proto

namespace Sugarcrm\Apis\Iam\Policy\V1alpha;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>sugarcrm.apis.iam.policy.v1alpha.DeletePermissionRequest</code>
 */
class DeletePermissionRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Name of the permission as an SRN
     *
     * Generated from protobuf field <code>string name = 1;</code>
     */
    private $name = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $name
     *           Name of the permission as an SRN
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Apis\Iam\Policy\V1Alpha\Permission::initOnce();
        parent::__construct($data);
    }

    /**
     * Name of the permission as an SRN
     *
     * Generated from protobuf field <code>string name = 1;</code>
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Name of the permission as an SRN
     *
     * Generated from protobuf field <code>string name = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setName($var)
    {
        GPBUtil::checkString($var, True);
        $this->name = $var;

        return $this;
    }

}

