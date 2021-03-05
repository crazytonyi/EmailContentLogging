<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: apis/rpc/error_details.proto

namespace Sugarcrm\Apis\Rpc;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Provides a localized error message that is safe to return to the user
 * which can be attached to an RPC error.
 *
 * Generated from protobuf message <code>sugarcrm.apis.rpc.LocalizedMessage</code>
 */
class LocalizedMessage extends \Google\Protobuf\Internal\Message
{
    /**
     * The locale used following the specification defined at
     * http://www.rfc-editor.org/rfc/bcp/bcp47.txt.
     * Examples are: "en-US", "fr-CH", "es-MX"
     *
     * Generated from protobuf field <code>string locale = 1;</code>
     */
    private $locale = '';
    /**
     * The localized error message in the above locale.
     *
     * Generated from protobuf field <code>string message = 2;</code>
     */
    private $message = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $locale
     *           The locale used following the specification defined at
     *           http://www.rfc-editor.org/rfc/bcp/bcp47.txt.
     *           Examples are: "en-US", "fr-CH", "es-MX"
     *     @type string $message
     *           The localized error message in the above locale.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Apis\Rpc\ErrorDetails::initOnce();
        parent::__construct($data);
    }

    /**
     * The locale used following the specification defined at
     * http://www.rfc-editor.org/rfc/bcp/bcp47.txt.
     * Examples are: "en-US", "fr-CH", "es-MX"
     *
     * Generated from protobuf field <code>string locale = 1;</code>
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * The locale used following the specification defined at
     * http://www.rfc-editor.org/rfc/bcp/bcp47.txt.
     * Examples are: "en-US", "fr-CH", "es-MX"
     *
     * Generated from protobuf field <code>string locale = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setLocale($var)
    {
        GPBUtil::checkString($var, True);
        $this->locale = $var;

        return $this;
    }

    /**
     * The localized error message in the above locale.
     *
     * Generated from protobuf field <code>string message = 2;</code>
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * The localized error message in the above locale.
     *
     * Generated from protobuf field <code>string message = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setMessage($var)
    {
        GPBUtil::checkString($var, True);
        $this->message = $var;

        return $this;
    }

}

