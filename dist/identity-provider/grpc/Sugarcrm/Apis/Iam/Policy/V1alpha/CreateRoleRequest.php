<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: apis/iam/policy/v1alpha/role.proto

namespace Sugarcrm\Apis\Iam\Policy\V1alpha;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>sugarcrm.apis.iam.policy.v1alpha.CreateRoleRequest</code>
 */
class CreateRoleRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>.sugarcrm.apis.iam.resource.v1.Role role = 1;</code>
     */
    private $role = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Sugarcrm\Apis\Iam\Resource\V1\Role $role
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Apis\Iam\Policy\V1Alpha\Role::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>.sugarcrm.apis.iam.resource.v1.Role role = 1;</code>
     * @return \Sugarcrm\Apis\Iam\Resource\V1\Role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Generated from protobuf field <code>.sugarcrm.apis.iam.resource.v1.Role role = 1;</code>
     * @param \Sugarcrm\Apis\Iam\Resource\V1\Role $var
     * @return $this
     */
    public function setRole($var)
    {
        GPBUtil::checkMessage($var, \Sugarcrm\Apis\Iam\Resource\V1\Role::class);
        $this->role = $var;

        return $this;
    }

}
