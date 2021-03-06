<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail;

use Laminas\Mail;
use Laminas\Mail\Header;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Mail
 * @covers \Laminas\Mail\Headers<extended>
 */
class HeadersTest extends TestCase
{
    public function testHeadersImplementsProperClasses()
    {
        $headers = new Mail\Headers();
        $this->assertInstanceOf('Iterator', $headers);
        $this->assertInstanceOf('Countable', $headers);
    }

    public function testHeadersFromStringFactoryCreatesSingleObject()
    {
        $headers = Mail\Headers::fromString("Fake: foo-bar");
        $this->assertEquals(1, $headers->count());

        $header = $headers->get('fake');
        $this->assertInstanceOf('Laminas\Mail\Header\GenericHeader', $header);
        $this->assertEquals('Fake', $header->getFieldName());
        $this->assertEquals('foo-bar', $header->getFieldValue());
    }

    public function testHeadersFromStringFactoryHandlesMissingWhitespace()
    {
        $headers = Mail\Headers::fromString("Fake:foo-bar");
        $this->assertEquals(1, $headers->count());

        $header = $headers->get('fake');
        $this->assertInstanceOf('Laminas\Mail\Header\GenericHeader', $header);
        $this->assertEquals('Fake', $header->getFieldName());
        $this->assertEquals('foo-bar', $header->getFieldValue());
    }

    /**
     * @group 6657
     */
    public function testHeadersFromStringFactoryCreatesSingleObjectWithContinuationLine()
    {
        $headers = Mail\Headers::fromString("Fake: foo-bar,\r\n      blah-blah");
        $this->assertEquals(1, $headers->count());

        $header = $headers->get('fake');
        $this->assertInstanceOf('Laminas\Mail\Header\GenericHeader', $header);
        $this->assertEquals('Fake', $header->getFieldName());
        $this->assertEquals('foo-bar, blah-blah', $header->getFieldValue());
    }

    public function testHeadersFromStringFactoryCreatesSingleObjectWithHeaderBreakLine()
    {
        $headers = Mail\Headers::fromString("Fake: foo-bar\r\n\r\n");
        $this->assertEquals(1, $headers->count());

        $header = $headers->get('fake');
        $this->assertInstanceOf('Laminas\Mail\Header\GenericHeader', $header);
        $this->assertEquals('Fake', $header->getFieldName());
        $this->assertEquals('foo-bar', $header->getFieldValue());
    }

    public function testHeadersFromStringFactoryThrowsExceptionOnMalformedHeaderLine()
    {
        $this->expectException('Laminas\Mail\Exception\RuntimeException');
        $this->expectExceptionMessage('does not match');
        Mail\Headers::fromString("Fake = foo-bar\r\n\r\n");
    }

    public function testHeadersFromStringFactoryThrowsExceptionOnMalformedHeaderLines()
    {
        $this->expectException('Laminas\Mail\Exception\RuntimeException');
        $this->expectExceptionMessage('Malformed header detected');
        Mail\Headers::fromString("Fake: foo-bar\r\n\r\n\r\n\r\nAnother-Fake: boo-baz");
    }

    public function testHeadersFromStringFactoryCreatesMultipleObjects()
    {
        $headers = Mail\Headers::fromString("Fake: foo-bar\r\nAnother-Fake: boo-baz");
        $this->assertEquals(2, $headers->count());

        $header = $headers->get('fake');
        $this->assertInstanceOf('Laminas\Mail\Header\GenericHeader', $header);
        $this->assertEquals('Fake', $header->getFieldName());
        $this->assertEquals('foo-bar', $header->getFieldValue());

        $header = $headers->get('anotherfake');
        $this->assertInstanceOf('Laminas\Mail\Header\GenericHeader', $header);
        $this->assertEquals('Another-Fake', $header->getFieldName());
        $this->assertEquals('boo-baz', $header->getFieldValue());
    }

    public function testPluginClassLoaderAccessors()
    {
        $headers = new Mail\Headers();
        $pcl = new Header\HeaderLoader();
        $headers->setPluginClassLoader($pcl);
        $this->assertSame($pcl, $headers->getPluginClassLoader());
    }

    public function testHeadersFromStringMultiHeaderWillAggregateLazyLoadedHeaders()
    {
        $headers = new Mail\Headers();
        /* @var $pcl \Laminas\Loader\PluginClassLoader */
        $pcl = $headers->getPluginClassLoader();
        $pcl->registerPlugin('foo', 'Laminas\Mail\Header\GenericMultiHeader');
        $headers->addHeaderLine('foo: bar1,bar2,bar3');
        $headers->forceLoading();
        $this->assertEquals(3, $headers->count());
    }

    public function testHeadersHasAndGetWorkProperly()
    {
        $headers = new Mail\Headers();
        $headers->addHeaders([
            $f = new Header\GenericHeader('Foo', 'bar'),
            new Header\GenericHeader('Baz', 'baz'),
        ]);
        $this->assertFalse($headers->has('foobar'));
        $this->assertTrue($headers->has('foo'));
        $this->assertTrue($headers->has('Foo'));
        $this->assertEquals('bar', $headers->get('foo')->getFieldValue());
    }

    public function testHeadersAggregatesHeaderObjects()
    {
        $fakeHeader = new Header\GenericHeader('Fake', 'bar');
        $headers = new Mail\Headers();
        $headers->addHeader($fakeHeader);
        $this->assertEquals(1, $headers->count());
        $this->assertEquals('bar', $headers->get('Fake')->getFieldValue());
    }

    public function testHeadersAggregatesHeaderThroughAddHeader()
    {
        $headers = new Mail\Headers();
        $headers->addHeader(new Header\GenericHeader('Fake', 'bar'));
        $this->assertEquals(1, $headers->count());
        $this->assertInstanceOf('Laminas\Mail\Header\GenericHeader', $headers->get('Fake'));
    }

    public function testHeadersAggregatesHeaderThroughAddHeaderLine()
    {
        $headers = new Mail\Headers();
        $headers->addHeaderLine('Fake', 'bar');
        $this->assertEquals(1, $headers->count());
        $this->assertInstanceOf('Laminas\Mail\Header\GenericHeader', $headers->get('Fake'));
    }

    public function testHeadersAddHeaderLineThrowsExceptionOnMissingFieldValue()
    {
        $this->expectException('Laminas\Mail\Header\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Header must match with the format "name:value"');
        $headers = new Mail\Headers();
        $headers->addHeaderLine('Foo');
    }

    public function testHeadersAddHeaderLineThrowsExceptionOnInvalidFieldNull()
    {
        $headers = new Mail\Headers();

        $this->expectException('Laminas\Mail\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('expects its first argument to be a string');
        $headers->addHeaderLine(null);
    }

    public function testHeadersAddHeaderLineThrowsExceptionOnInvalidFieldObject()
    {
        $headers = new Mail\Headers();
        $object = new \stdClass();

        $this->expectException('Laminas\Mail\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('expects its first argument to be a string');
        $headers->addHeaderLine($object);
    }

    public function testHeadersAggregatesHeadersThroughAddHeaders()
    {
        $headers = new Mail\Headers();
        $headers->addHeaders([new Header\GenericHeader('Foo', 'bar'), new Header\GenericHeader('Baz', 'baz')]);
        $this->assertEquals(2, $headers->count());
        $this->assertInstanceOf('Laminas\Mail\Header\GenericHeader', $headers->get('Foo'));
        $this->assertEquals('bar', $headers->get('foo')->getFieldValue());
        $this->assertEquals('baz', $headers->get('baz')->getFieldValue());

        $headers = new Mail\Headers();
        $headers->addHeaders(['Foo: bar', 'Baz: baz']);
        $this->assertEquals(2, $headers->count());
        $this->assertInstanceOf('Laminas\Mail\Header\GenericHeader', $headers->get('Foo'));
        $this->assertEquals('bar', $headers->get('foo')->getFieldValue());
        $this->assertEquals('baz', $headers->get('baz')->getFieldValue());

        $headers = new Mail\Headers();
        $headers->addHeaders([['Foo' => 'bar'], ['Baz' => 'baz']]);
        $this->assertEquals(2, $headers->count());
        $this->assertInstanceOf('Laminas\Mail\Header\GenericHeader', $headers->get('Foo'));
        $this->assertEquals('bar', $headers->get('foo')->getFieldValue());
        $this->assertEquals('baz', $headers->get('baz')->getFieldValue());

        $headers = new Mail\Headers();
        $headers->addHeaders([['Foo', 'bar'], ['Baz', 'baz']]);
        $this->assertEquals(2, $headers->count());
        $this->assertInstanceOf('Laminas\Mail\Header\GenericHeader', $headers->get('Foo'));
        $this->assertEquals('bar', $headers->get('foo')->getFieldValue());
        $this->assertEquals('baz', $headers->get('baz')->getFieldValue());

        $headers = new Mail\Headers();
        $headers->addHeaders(['Foo' => 'bar', 'Baz' => 'baz']);
        $this->assertEquals(2, $headers->count());
        $this->assertInstanceOf('Laminas\Mail\Header\GenericHeader', $headers->get('Foo'));
        $this->assertEquals('bar', $headers->get('foo')->getFieldValue());
        $this->assertEquals('baz', $headers->get('baz')->getFieldValue());
    }

    public function testHeadersAddHeadersThrowsExceptionOnInvalidArguments()
    {
        $this->expectException('Laminas\Mail\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Expected array or Trav');
        $headers = new Mail\Headers();
        $headers->addHeaders('foo');
    }

    public function testHeadersCanRemoveHeader()
    {
        $headers = new Mail\Headers();
        $headers->addHeaders(['Foo' => 'bar', 'Baz' => 'baz']);
        $this->assertEquals(2, $headers->count());
        $headers->removeHeader('foo');
        $this->assertEquals(1, $headers->count());
        $this->assertFalse($headers->has('foo'));
        $this->assertTrue($headers->has('baz'));
    }

    public function testRemoveHeaderWithFieldNameWillRemoveAllInstances()
    {
        $headers = new Mail\Headers();
        $headers->addHeaders([['Foo' => 'foo'], ['Foo' => 'bar'], 'Baz' => 'baz']);
        $this->assertEquals(3, $headers->count());
        $headers->removeHeader('foo');
        $this->assertEquals(1, $headers->count());
        $this->assertFalse($headers->get('foo'));
        $this->assertTrue($headers->has('baz'));
    }

    public function testRemoveHeaderWithInstanceWillRemoveThatInstance()
    {
        $headers = new Mail\Headers();
        $headers->addHeaders([['Foo' => 'foo'], ['Foo' => 'bar'], 'Baz' => 'baz']);
        $header = $headers->get('foo')->current();
        $this->assertEquals(3, $headers->count());
        $headers->removeHeader($header);
        $this->assertEquals(2, $headers->count());
        $this->assertTrue($headers->has('foo'));
        $this->assertNotSame($header, $headers->get('foo'));
    }

    public function testRemoveHeaderWhenEmpty()
    {
        $headers = new Mail\Headers();
        $this->assertFalse($headers->removeHeader(null));
    }

    public function testHeadersCanClearAllHeaders()
    {
        $headers = new Mail\Headers();
        $headers->addHeaders(['Foo' => 'bar', 'Baz' => 'baz']);
        $this->assertEquals(2, $headers->count());
        $headers->clearHeaders();
        $this->assertEquals(0, $headers->count());
    }

    public function testHeadersCanBeIterated()
    {
        $headers = new Mail\Headers();
        $headers->addHeaders(['Foo' => 'bar', 'Baz' => 'baz']);
        $iterations = 0;
        foreach ($headers as $index => $header) {
            $iterations++;
            $this->assertInstanceOf('Laminas\Mail\Header\GenericHeader', $header);
            switch ($index) {
                case 0:
                    $this->assertEquals('bar', $header->getFieldValue());
                    break;
                case 1:
                    $this->assertEquals('baz', $header->getFieldValue());
                    break;
                default:
                    $this->fail('Invalid index returned from iterator');
            }
        }
        $this->assertEquals(2, $iterations);
    }

    public function testHeadersCanBeCastToString()
    {
        $headers = new Mail\Headers();
        $headers->addHeaders(['Foo' => 'bar', 'Baz' => 'baz']);
        $this->assertEquals('Foo: bar' . "\r\n" . 'Baz: baz' . "\r\n", $headers->toString());
    }

    public function testHeadersCanBeCastToArray()
    {
        $headers = new Mail\Headers();
        $headers->addHeaders(['Foo' => 'bar', 'Baz' => 'baz']);
        $this->assertEquals(['Foo' => 'bar', 'Baz' => 'baz'], $headers->toArray());
    }

    public function testCastingToArrayReturnsMultiHeadersAsArrays()
    {
        $headers = new Mail\Headers();

        // @codingStandardsIgnoreStart
        $received1 = Header\Received::fromString("Received: from framework (localhost [127.0.0.1])\r\n by framework (Postfix) with ESMTP id BBBBBBBBBBB\r\n for <laminas@framework>; Mon, 21 Nov 2011 12:50:27 -0600 (CST)");
        $received2 = Header\Received::fromString("Received: from framework (localhost [127.0.0.1])\r\n by framework (Postfix) with ESMTP id AAAAAAAAAAA\r\n for <laminas@framework>; Mon, 21 Nov 2011 12:50:29 -0600 (CST)");
        // @codingStandardsIgnoreEnd

        $headers->addHeader($received1);
        $headers->addHeader($received2);
        $array   = $headers->toArray();
        $expected = [
            'Received' => [
                $received1->getFieldValue(),
                $received2->getFieldValue(),
            ],
        ];
        $this->assertEquals($expected, $array);
    }

    public function testCastingToStringReturnsAllMultiHeaderValues()
    {
        $headers = new Mail\Headers();

        // @codingStandardsIgnoreStart
        $received1 = Header\Received::fromString("Received: from framework (localhost [127.0.0.1])\r\n by framework (Postfix) with ESMTP id BBBBBBBBBBB\r\n for <laminas@framework>; Mon, 21 Nov 2011 12:50:27 -0600 (CST)");
        $received2 = Header\Received::fromString("Received: from framework (localhost [127.0.0.1])\r\n by framework (Postfix) with ESMTP id AAAAAAAAAAA\r\n for <laminas@framework>; Mon, 21 Nov 2011 12:50:29 -0600 (CST)");
        // @codingStandardsIgnoreEnd

        $headers->addHeader($received1);
        $headers->addHeader($received2);
        $string  = $headers->toString();
        $expected = [
            'Received: ' . $received1->getFieldValue(),
            'Received: ' . $received2->getFieldValue(),
        ];
        $expected = implode("\r\n", $expected) . "\r\n";
        $this->assertEquals($expected, $string);
    }

    public function testGetReturnsArrayIterator()
    {
        $headers = new Mail\Headers();
        $received = Header\Received::fromString('Received: from framework (localhost [127.0.0.1])');
        $headers->addHeader($received);

        $return = $headers->get('Received');
        $this->assertSame('ArrayIterator', \get_class($return));
    }

    /**
     * @test that toArray can take format parameter
     * @link https://github.com/zendframework/zend-mail/pull/61
     */
    public function testToArrayFormatRaw()
    {
        $raw_subject = '=?ISO-8859-2?Q?PD=3A_My=3A_Go=B3?= =?ISO-8859-2?Q?blahblah?=';
        $headers = new Mail\Headers();
        $subject = Header\Subject::fromString("Subject: $raw_subject");
        $headers->addHeader($subject);
        // default
        $array = $headers->toArray(Header\HeaderInterface::FORMAT_RAW);
        $expected = [
            'Subject' => 'PD: My: Go??blahblah',
        ];
        $this->assertEquals($expected, $array);
    }

    /**
     * @test that toArray can take format parameter
     * @link https://github.com/zendframework/zend-mail/pull/61
     */
    public function testToArrayFormatEncoded()
    {
        $raw_subject = '=?ISO-8859-2?Q?PD=3A_My=3A_Go=B3?= =?ISO-8859-2?Q?blahblah?=';
        $headers = new Mail\Headers();
        $subject = Header\Subject::fromString("Subject: $raw_subject");
        $headers->addHeader($subject);

        // encoded
        $array = $headers->toArray(Header\HeaderInterface::FORMAT_ENCODED);
        $expected = [
            'Subject' => '=?UTF-8?Q?PD:=20My:=20Go=C5=82blahblah?=',
        ];
        $this->assertEquals($expected, $array);
    }

    public static function expectedHeaders()
    {
        return [
            ['bcc', 'Laminas\Mail\Header\Bcc'],
            ['cc', 'Laminas\Mail\Header\Cc'],
            ['contenttype', 'Laminas\Mail\Header\ContentType'],
            ['content_type', 'Laminas\Mail\Header\ContentType'],
            ['content-type', 'Laminas\Mail\Header\ContentType'],
            ['date', 'Laminas\Mail\Header\Date'],
            ['from', 'Laminas\Mail\Header\From'],
            ['mimeversion', 'Laminas\Mail\Header\MimeVersion'],
            ['mime_version', 'Laminas\Mail\Header\MimeVersion'],
            ['mime-version', 'Laminas\Mail\Header\MimeVersion'],
            ['received', 'Laminas\Mail\Header\Received'],
            ['replyto', 'Laminas\Mail\Header\ReplyTo'],
            ['reply_to', 'Laminas\Mail\Header\ReplyTo'],
            ['reply-to', 'Laminas\Mail\Header\ReplyTo'],
            ['sender', 'Laminas\Mail\Header\Sender'],
            ['subject', 'Laminas\Mail\Header\Subject'],
            ['to', 'Laminas\Mail\Header\To'],
        ];
    }

    /**
     * @dataProvider expectedHeaders
     */
    public function testDefaultPluginLoaderIsSeededWithHeaders($plugin, $class)
    {
        $headers = new Mail\Headers();
        $loader  = $headers->getPluginClassLoader();
        $test    = $loader->load($plugin);
        $this->assertEquals($class, $test);
    }

    public function testClone()
    {
        $headers = new Mail\Headers();
        $headers->addHeader(new Header\Bcc());
        $headers2 = clone($headers);
        $this->assertEquals($headers, $headers2);
        $headers2->removeHeader('Bcc');
        $this->assertTrue($headers->has('Bcc'));
        $this->assertFalse($headers2->has('Bcc'));
    }

    /**
     * @group ZF2015-04
     */
    public function testHeaderCrLfAttackFromString()
    {
        $this->expectException('Laminas\Mail\Exception\RuntimeException');
        Mail\Headers::fromString("Fake: foo-bar\r\n\r\nevilContent");
    }

    /**
     * @group ZF2015-04
     */
    public function testHeaderCrLfAttackAddHeaderLineSingle()
    {
        $headers = new Mail\Headers();
        $this->expectException('Laminas\Mail\Header\Exception\InvalidArgumentException');
        $headers->addHeaderLine("Fake: foo-bar\r\n\r\nevilContent");
    }

    /**
     * @group ZF2015-04
     */
    public function testHeaderCrLfAttackAddHeaderLineWithValue()
    {
        $headers = new Mail\Headers();
        $this->expectException('Laminas\Mail\Header\Exception\InvalidArgumentException');
        $headers->addHeaderLine('Fake', "foo-bar\r\n\r\nevilContent");
    }

    /**
     * @group ZF2015-04
     */
    public function testHeaderCrLfAttackAddHeaderLineMultiple()
    {
        $headers = new Mail\Headers();
        $this->expectException('Laminas\Mail\Header\Exception\InvalidArgumentException');
        $headers->addHeaderLine('Fake', ["foo-bar\r\n\r\nevilContent"]);
        $headers->forceLoading();
    }

    /**
     * @group ZF2015-04
     */
    public function testHeaderCrLfAttackAddHeadersSingle()
    {
        $headers = new Mail\Headers();
        $this->expectException('Laminas\Mail\Header\Exception\InvalidArgumentException');
        $headers->addHeaders(["Fake: foo-bar\r\n\r\nevilContent"]);
    }

    /**
     * @group ZF2015-04
     */
    public function testHeaderCrLfAttackAddHeadersWithValue()
    {
        $headers = new Mail\Headers();
        $this->expectException('Laminas\Mail\Header\Exception\InvalidArgumentException');
        $headers->addHeaders(['Fake' => "foo-bar\r\n\r\nevilContent"]);
    }

    /**
     * @group ZF2015-04
     */
    public function testHeaderCrLfAttackAddHeadersMultiple()
    {
        $headers = new Mail\Headers();
        $this->expectException('Laminas\Mail\Header\Exception\InvalidArgumentException');
        $headers->addHeaders(['Fake' => ["foo-bar\r\n\r\nevilContent"]]);
        $headers->forceLoading();
    }

    public function testAddressListGetEncodedFieldValueWithUtf8Domain()
    {
        $to = new Header\To;
        $to->setEncoding('UTF-8');
        $to->getAddressList()->add('local-part@??-umlaut.de');
        $encodedValue = $to->getFieldValue(Header\HeaderInterface::FORMAT_ENCODED);
        $this->assertEquals('local-part@xn---umlaut-4wa.de', $encodedValue);
    }

    /**
     * Test ">" being part of email "comment".
     *
     * Example Email-header:
     *  "Foo <bar" foo.bar@test.com
     *
     * Description:
     *   The example email-header should be valid
     *   according to https://tools.ietf.org/html/rfc2822#section-3.4
     *   but the function AdressList.php/addFromString matches it incorrect.
     *   The result has the following form:
     *    "bar <foo.bar@test.com"
     *   This is clearly not a valid adress and therefore causes
     *   exceptions in the following code
     *
     * @see https://github.com/zendframework/zend-mail/issues/127
     */
    public function testEmailNameParser()
    {
        $to = Header\To::fromString('To: "=?UTF-8?Q?=C3=B5lu?= <bar" <foo.bar@test.com>');

        $address = $to->getAddressList()->get('foo.bar@test.com');
        $this->assertEquals('??lu <bar', $address->getName());
        $this->assertEquals('foo.bar@test.com', $address->getEmail());

        $encodedValue = $to->getFieldValue(Header\HeaderInterface::FORMAT_ENCODED);
        $this->assertEquals('=?UTF-8?Q?=C3=B5lu=20<bar?= <foo.bar@test.com>', $encodedValue);

        $encodedValue = $to->getFieldValue(Header\HeaderInterface::FORMAT_RAW);
        // FIXME: shouldn't the "name" part be in quotes?
        $this->assertEquals('??lu <bar <foo.bar@test.com>', $encodedValue);
    }

    public function testDefaultEncoding()
    {
        $headers = new Mail\Headers();
        $this->assertSame('ASCII', $headers->getEncoding());
    }

    public function testSetEncodingNoHeaders()
    {
        $headers = new Mail\Headers();
        $headers->setEncoding('UTF-8');
        $this->assertSame('UTF-8', $headers->getEncoding());
    }

    public function testSetEncodingWithHeaders()
    {
        $headers = new Mail\Headers();
        $headers->addHeaderLine('To: test@example.com');
        $headers->addHeaderLine('Cc: tester@example.org');

        $headers->setEncoding('UTF-8');
        $this->assertSame('UTF-8', $headers->getEncoding());
    }

    public function testAddHeaderCallsSetEncoding()
    {
        $headers = new Mail\Headers();
        $headers->setEncoding('UTF-8');

        $subject = new Header\Subject('test subject');
        // default to ASCII
        $this->assertSame('ASCII', $subject->getEncoding());

        $headers->addHeader($subject);
        // now UTF-8 via addHeader() call
        $this->assertSame('UTF-8', $subject->getEncoding());
    }
}
