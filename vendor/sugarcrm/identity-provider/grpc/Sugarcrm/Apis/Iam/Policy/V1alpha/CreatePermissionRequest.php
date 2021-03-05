<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: apis/iam/policy/v1alpha/permission.proto

namespace Sugarcrm\Apis\Iam\Policy\V1alpha;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>sugarcrm.apis.iam.policy.v1alpha.CreatePermissionRequest</code>
 */
class CreatePermissionRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>.sugarcrm.apis.iam.resource.v1.Permission permission = 1;</code>
     */
    private $permission = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Sugarcrm\Apis\Iam\Resource\V1\Permission $permission
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Apis\Iam\Policy\V1Alpha\Permission::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>.sugarcrm.apis.iam.resource.v1.Permission permission = 1;</code>
     * @return \Sugarcrm\Apis\Iam\Resource\V1\Permission
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * Generated from protobuf field <code>.sugarcrm.apis.iam.resource.v1.Permission permission = 1;</code>
     * @param \Sugarcrm\Apis\Iam\Resource\V1\Permission $var
     * @return $this
     */
    public function setPermission($var)
    {
        GPBUtil::checkMessage($var, \Sugarcrm\Apis\Iam\Resource\V1\Permission::class);
        $this->permission = $var;

        return $this;
    }

}
