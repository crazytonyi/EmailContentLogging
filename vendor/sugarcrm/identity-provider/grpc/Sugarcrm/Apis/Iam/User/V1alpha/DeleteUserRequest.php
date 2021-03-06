<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: apis/iam/user/v1alpha/user.proto

namespace Sugarcrm\Apis\Iam\User\V1alpha;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>sugarcrm.apis.iam.user.v1alpha.DeleteUserRequest</code>
 */
class DeleteUserRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>string name = 1;</code>
     */
    private $name = '';
    /**
     * If true sending data to user sync service will be disabled
     *
     * Generated from protobuf field <code>bool disable_user_sync = 2;</code>
     */
    private $disable_user_sync = false;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $name
     *     @type bool $disable_user_sync
     *           If true sending data to user sync service will be disabled
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Apis\Iam\User\V1Alpha\User::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>string name = 1;</code>
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
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

    /**
     * If true sending data to user sync service will be disabled
     *
     * Generated from protobuf field <code>bool disable_user_sync = 2;</code>
     * @return bool
     */
    public function getDisableUserSync()
    {
        return $this->disable_user_sync;
    }

    /**
     * If true sending data to user sync service will be disabled
     *
     * Generated from protobuf field <code>bool disable_user_sync = 2;</code>
     * @param bool $var
     * @return $this
     */
    public function setDisableUserSync($var)
    {
        GPBUtil::checkBool($var);
        $this->disable_user_sync = $var;

        return $this;
    }

}

