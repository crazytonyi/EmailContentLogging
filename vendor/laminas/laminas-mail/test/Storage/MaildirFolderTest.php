<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Storage;

use Laminas\Config;
use Laminas\Mail\Storage\Folder;
use PHPUnit\Framework\TestCase;
use RecursiveIteratorIterator;

/**
 * @group      Laminas_Mail
 */
class MaildirFolderTest extends TestCase
{
    protected $params;
    protected $tmpdir;
    protected $subdirs = ['.', '.subfolder', '.subfolder.test'];

    public function setUp()
    {
        if (\strtoupper(\substr(PHP_OS, 0, 3)) == 'WIN') {
            $this->markTestSkipped('This test does not work on Windows');
            return;
        }

        $originalMaildir = __DIR__ . '/../_files/test.maildir/';

        if ($this->tmpdir == null) {
            if (getenv('TESTS_LAMINAS_MAIL_TEMPDIR') != null) {
                $this->tmpdir = getenv('TESTS_LAMINAS_MAIL_TEMPDIR');
            } else {
                $this->tmpdir = __DIR__ . '/../_files/test.tmp/';
            }
            if (! file_exists($this->tmpdir)) {
                mkdir($this->tmpdir);
            }
            $count = 0;
            $dh = opendir($this->tmpdir);
            while (readdir($dh) !== false) {
                ++$count;
            }
            closedir($dh);
            if ($count != 2) {
                $this->markTestSkipped('Are you sure your tmp dir is a valid empty dir?');
                return;
            }
        }

        if (! \file_exists($originalMaildir . 'maildirsize') && \class_exists('PharData')) {
            try {
                $phar = new \PharData($originalMaildir . 'maildir.tar');
                $phar->extractTo($originalMaildir);
                // This empty directory is in the tar, but not unpacked by PharData
                \mkdir($originalMaildir . '.subfolder/cur');
            } catch (\Exception $e) {
                // intentionally empty catch block
            }
        }

        if (! \file_exists($originalMaildir . 'maildirsize')) {
            $this->markTestSkipped('You have to unpack maildir.tar in '
            . 'Laminas/Mail/_files/test.maildir/ directory to run the maildir tests');
            return;
        }

        $this->params = [];
        $this->params['dirname'] = $this->tmpdir;

        foreach ($this->subdirs as $dir) {
            if ($dir != '.') {
                mkdir($this->tmpdir . $dir);
            }
            foreach (['cur', 'new'] as $subdir) {
                if (! file_exists($originalMaildir . $dir . '/' . $subdir)) {
                    continue;
                }
                mkdir($this->tmpdir . $dir . '/' . $subdir);
                $dh = opendir($originalMaildir . $dir . '/' . $subdir);
                while (($entry = readdir($dh)) !== false) {
                    $entry = $dir . '/' . $subdir . '/' . $entry;
                    if (! is_file($originalMaildir . $entry)) {
                        continue;
                    }
                    copy($originalMaildir . $entry, $this->tmpdir . $entry);
                }
                closedir($dh);
            }
        }
    }

    public function tearDown()
    {
        \chmod($this->tmpdir, 0700);
        foreach (array_reverse($this->subdirs) as $dir) {
            foreach (['cur', 'new'] as $subdir) {
                if (! file_exists($this->tmpdir . $dir . '/' . $subdir)) {
                    continue;
                }
                if (! is_dir($this->tmpdir . $dir . '/' . $subdir)) {
                    continue;
                }
                $dh = opendir($this->tmpdir . $dir . '/' . $subdir);
                while (($entry = readdir($dh)) !== false) {
                    $entry = $this->tmpdir . $dir . '/' . $subdir . '/' . $entry;
                    if (! is_file($entry)) {
                        continue;
                    }
                    unlink($entry);
                }
                closedir($dh);
                rmdir($this->tmpdir . $dir . '/' . $subdir);
            }
            if ($dir != '.' && is_dir($this->tmpdir . $dir)) {
                rmdir($this->tmpdir . $dir);
            }
        }
    }

    public function testLoadOk()
    {
        $mail = new Folder\Maildir($this->params);
        $this->assertSame(Folder\Maildir::class, \get_class($mail));
    }

    public function testLoadConfig()
    {
        $mail = new Folder\Maildir(new Config\Config($this->params));
        $this->assertSame(Folder\Maildir::class, \get_class($mail));
    }

    public function testNoParams()
    {
        $this->expectException('Laminas\Mail\Storage\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('no valid dirname given in params');
        new Folder\Maildir([]);
    }

    public function testLoadFailure()
    {
        $this->expectException('Laminas\Mail\Storage\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('no valid dirname given in params');
        new Folder\Maildir(['dirname' => 'This/Folder/Does/Not/Exist']);
    }

    public function testLoadUnkownFolder()
    {
        $this->params['folder'] = 'UnknownFolder';
        $this->expectException('Laminas\Mail\Storage\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('no subfolder named UnknownFolder');
        new Folder\Maildir($this->params);
    }

    public function testChangeFolder()
    {
        $this->markTestIncomplete("Fail");
        $mail = new Folder\Maildir($this->params);

        $mail->selectFolder('subfolder.test');

        $this->assertEquals($mail->getCurrentFolder(), 'subfolder.test');
    }

    public function testUnknownFolder()
    {
        $mail = new Folder\Maildir($this->params);

        $this->expectException('Laminas\Mail\Storage\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('no subfolder named /Unknown/Folder/');
        $mail->selectFolder('/Unknown/Folder/');
    }

    public function testGlobalName()
    {
        $this->markTestIncomplete("Fail");
        $mail = new Folder\Maildir($this->params);

        $this->assertEquals($mail->getFolders()->subfolder->__toString(), 'subfolder');
    }

    public function testLocalName()
    {
        $this->markTestIncomplete("Fail");
        $mail = new Folder\Maildir($this->params);

        $this->assertEquals($mail->getFolders()->subfolder->key(), 'test');
    }

    public function testIterator()
    {
        $this->markTestIncomplete("Fail");
        $mail = new Folder\Maildir($this->params);
        $iterator = new RecursiveIteratorIterator($mail->getFolders(), RecursiveIteratorIterator::SELF_FIRST);
        // we search for this folder because we can't assume an order while iterating
        $search_folders = ['subfolder'      => 'subfolder',
                                'subfolder.test' => 'test',
                                'INBOX'          => 'INBOX'];
        $found_folders = [];

        foreach ($iterator as $localName => $folder) {
            if (! isset($search_folders[$folder->getGlobalName()])) {
                continue;
            }

            // explicit call of __toString() needed for PHP < 5.2
            $found_folders[$folder->__toString()] = $localName;
        }

        $this->assertEquals($search_folders, $found_folders);
    }

    public function testKeyLocalName()
    {
        $this->markTestIncomplete("Fail");
        $mail = new Folder\Maildir($this->params);
        $iterator = new RecursiveIteratorIterator($mail->getFolders(), RecursiveIteratorIterator::SELF_FIRST);
        // we search for this folder because we can't assume an order while iterating
        $search_folders = ['subfolder'      => 'subfolder',
                                'subfolder.test' => 'test',
                                'INBOX'          => 'INBOX'];
        $found_folders = [];

        foreach ($iterator as $localName => $folder) {
            if (! isset($search_folders[$folder->getGlobalName()])) {
                continue;
            }

            // explicit call of __toString() needed for PHP < 5.2
            $found_folders[$folder->__toString()] = $localName;
        }

        $this->assertEquals($search_folders, $found_folders);
    }

    public function testInboxEquals()
    {
        $this->markTestIncomplete("Fail");
        $mail = new Folder\Maildir($this->params);
        $iterator = new RecursiveIteratorIterator(
            $mail->getFolders('INBOX.subfolder'),
            RecursiveIteratorIterator::SELF_FIRST
        );
        // we search for this folder because we can't assume an order while iterating
        $search_folders = ['subfolder.test' => 'test'];
        $found_folders = [];

        foreach ($iterator as $localName => $folder) {
            if (! isset($search_folders[$folder->getGlobalName()])) {
                continue;
            }

            // explicit call of __toString() needed for PHP < 5.2
            $found_folders[$folder->__toString()] = $localName;
        }

        $this->assertEquals($search_folders, $found_folders);
    }

    public function testSelectable()
    {
        $mail = new Folder\Maildir($this->params);
        $iterator = new RecursiveIteratorIterator($mail->getFolders(), RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $localName => $folder) {
            $this->assertEquals($localName, $folder->getLocalName());
        }
    }


    public function testCount()
    {
        $this->markTestIncomplete("Fail");
        $mail = new Folder\Maildir($this->params);

        $count = $mail->countMessages();
        $this->assertEquals(5, $count);

        $mail->selectFolder('subfolder.test');
        $count = $mail->countMessages();
        $this->assertEquals(1, $count);
    }

    public function testSize()
    {
        $this->markTestIncomplete("Fail");
        $mail = new Folder\Maildir($this->params);
        $shouldSizes = [1 => 397, 89, 694, 452, 497];

        $sizes = $mail->getSize();
        $this->assertEquals($shouldSizes, $sizes);

        $mail->selectFolder('subfolder.test');
        $sizes = $mail->getSize();
        $this->assertEquals([1 => 467], $sizes);
    }

    public function testFetchHeader()
    {
        $this->markTestIncomplete("Fail");
        $mail = new Folder\Maildir($this->params);

        $subject = $mail->getMessage(1)->subject;
        $this->assertEquals('Simple Message', $subject);

        $mail->selectFolder('subfolder.test');
        $subject = $mail->getMessage(1)->subject;
        $this->assertEquals('Message in subfolder', $subject);
    }

    public function testNotReadableFolder()
    {
        $this->markTestIncomplete("Fail");
        $stat = stat($this->params['dirname'] . '.subfolder');
        chmod($this->params['dirname'] . '.subfolder', 0);
        clearstatcache();
        $statcheck = stat($this->params['dirname'] . '.subfolder');
        if ($statcheck['mode'] % (8 * 8 * 8) !== 0) {
            chmod($this->params['dirname'] . '.subfolder', $stat['mode']);
            $this->markTestSkipped(
                'cannot remove read rights, which makes this test useless (maybe you are using Windows?)'
            );
            return;
        }

        $check = false;
        try {
            $mail = new Folder\Maildir($this->params);
        } catch (\Exception $e) {
            $check = true;
            // test ok
        }

        chmod($this->params['dirname'] . '.subfolder', $stat['mode']);

        if (! $check) {
            $this->fail('no exception while loading invalid dir with subfolder not readable');
        }
    }

    public function testNotReadableMaildir()
    {
        chmod($this->params['dirname'], 0);

        $this->expectException('Laminas\Mail\Storage\Exception\RuntimeException');
        $this->expectExceptionMessage('can\'t read folders in maildir');
        new Folder\Maildir($this->params);
    }

    public function testGetInvalidFolder()
    {
        $mail = new Folder\Maildir($this->params);
        $root = $mail->getFolders();
        $root->foobar = new Folder('foobar', DIRECTORY_SEPARATOR . 'foobar');

        $this->expectException('Laminas\Mail\Storage\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('folder foobar not found');
        $mail->selectFolder('foobar');
    }

    public function testGetVanishedFolder()
    {
        $mail = new Folder\Maildir($this->params);
        $root = $mail->getFolders();
        $root->foobar = new Folder('foobar', 'foobar');

        $this->expectException('Laminas\Mail\Storage\Exception\RuntimeException');
        $this->expectExceptionMessage('seems like the maildir has vanished');
        $mail->selectFolder('foobar');
    }

    public function testGetNotSelectableFolder()
    {
        $mail = new Folder\Maildir($this->params);
        $root = $mail->getFolders();
        $root->foobar = new Folder('foobar', 'foobar', false);

        $this->expectException('Laminas\Mail\Storage\Exception\RuntimeException');
        $this->expectExceptionMessage('foobar is not selectable');
        $mail->selectFolder('foobar');
    }

    public function testWithAdditionalFolder()
    {
        mkdir($this->params['dirname'] . '.xyyx');
        mkdir($this->params['dirname'] . '.xyyx/cur');
        mkdir($this->params['dirname'] . '.xyyx/new');

        $mail = new Folder\Maildir($this->params);
        $mail->selectFolder('xyyx');
        $this->assertEquals($mail->countMessages(), 0);

        rmdir($this->params['dirname'] . '.xyyx/cur');
        rmdir($this->params['dirname'] . '.xyyx/new');
        rmdir($this->params['dirname'] . '.xyyx');
    }
}
