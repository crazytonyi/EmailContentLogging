<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: apis/iam/policy/v1alpha/permission.proto

namespace Sugarcrm\Apis\Iam\Policy\V1alpha;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>sugarcrm.apis.iam.policy.v1alpha.ListPermissionsResponse</code>
 */
class ListPermissionsResponse extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>repeated .sugarcrm.apis.iam.resource.v1.Permission permissions = 1;</code>
     */
    private $permissions;
    /**
     * Generated from protobuf field <code>string next_page_token = 2;</code>
     */
    private $next_page_token = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Sugarcrm\Apis\Iam\Resource\V1\Permission[]|\Google\Protobuf\Internal\RepeatedField $permissions
     *     @type string $next_page_token
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Apis\Iam\Policy\V1Alpha\Permission::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>repeated .sugarcrm.apis.iam.resource.v1.Permission permissions = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * Generated from protobuf field <code>repeated .sugarcrm.apis.iam.resource.v1.Permission permissions = 1;</code>
     * @param \Sugarcrm\Apis\Iam\Resource\V1\Permission[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setPermissions($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Sugarcrm\Apis\Iam\Resource\V1\Permission::class);
        $this->permissions = $arr;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string next_page_token = 2;</code>
     * @return string
     */
    public function getNextPageToken()
    {
        return $this->next_page_token;
    }

    /**
     * Generated from protobuf field <code>string next_page_token = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setNextPageToken($var)
    {
        GPBUtil::checkString($var, True);
        $this->next_page_token = $var;

        return $this;
    }

}
