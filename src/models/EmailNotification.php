<?php

namespace Solspace\ExpressForms\models;

use craft\base\Model;
use Solspace\ExpressForms\exceptions\EmailNotifications\CouldNotParseNotificationException;
use Solspace\ExpressForms\exceptions\EmailNotifications\EmailNotificationsException;
use Solspace\ExpressForms\exceptions\EmailNotifications\NotificationNotFound;
use Solspace\ExpressForms\exceptions\EmailNotifications\NotificationTemplateFolderNotSetException;
use Solspace\ExpressForms\objects\ParameterBag;
use Symfony\Component\Yaml\Yaml;

class EmailNotification extends Model
{
    const REQUIRED_METADATA = [
        'fromName',
        'fromEmail',
    ];

    const ALLOWED_FILE_EXTENSIONS = ['twig', 'html'];
    const DEFAULT_FILE_EXTENSION  = 'twig';

    /** @var string */
    public $name;

    /** @var string */
    private $description;

    /** @var string */
    public $fromName;

    /** @var string */
    public $fromEmail;

    /** @var string */
    public $replyTo;

    /** @var string */
    public $cc;

    /** @var string */
    public $bcc;

    /** @var string */
    public $subject;

    /** @var string */
    public $body;

    /** @var bool */
    public $includeAttachments = false;

    /** @var string */
    public $fileName;

    /** @var ParameterBag */
    private $parameterBag;

    /**
     * @param string $directory
     * @param string $fileName
     * @param bool   $overwrite
     *
     * @return EmailNotification
     * @throws EmailNotificationsException
     * @throws NotificationTemplateFolderNotSetException
     */
    public static function create(
        string $directory,
        string $fileName = 'template',
        bool $overwrite = false
    ): EmailNotification {
        if (!file_exists($directory) || !is_dir($directory)) {
            throw new NotificationTemplateFolderNotSetException('No email notification templates folder set');
        }

        $base = pathinfo($fileName, PATHINFO_FILENAME);
        $ext  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($ext, ['twig', 'html'])) {
            $ext = 'twig';
        }

        $i = 0;
        do {
            if ($i === 0) {
                $fileName = "$base.$ext";
            } else {
                $fileName = $base . "_$i.$ext";
            }

            $filePath = $directory . '/' . $fileName;
            $i++;

            if (!file_exists($filePath) || $overwrite) {
                break;
            }
        } while ($i < 100);

        if ($i >= 100) {
            throw new EmailNotificationsException('Could not create a new template');
        }

        $notification = new EmailNotification();
        $notification
            ->setFileName($fileName)
            ->setName('Email Notification Template')
            ->setFromName('{{ craft.app.systemSettings.getSettings("email").fromName }}')
            ->setFromEmail('{{ craft.app.systemSettings.getSettings("email").fromEmail }}')
            ->setReplyTo('{{ craft.app.systemSettings.getSettings("email").fromEmail }}')
            ->setSubject('New submission from your {{ form.name }} form')
            ->setIncludeAttachments(true)
            ->setDescription('A description of what this template does.')
            ->setBody(
                <<<DATA
<p>The following submission came in on {{ dateCreated|date('l, F j, Y \\\\a\\\\t g:ia') }}.</p>

<ul>
    {% for field in form.fields %}
        <li>{{ field.label }}: {{ field.valueAsString }}</li>
    {% endfor %}
</ul>
DATA
            );

        return $notification;
    }

    /**
     * @param string|null $filePath
     *
     * @return EmailNotification
     * @throws CouldNotParseNotificationException
     * @throws EmailNotificationsException
     * @throws NotificationNotFound
     */
    public static function fromFile(string $filePath = null): EmailNotification
    {
        if (null === $filePath) {
            throw new EmailNotificationsException('No path specified');
        }

        $notification = new EmailNotification();
        $notification->setFileName(pathinfo($filePath, PATHINFO_BASENAME));

        try {
            $content = file_get_contents($filePath);
        } catch (\Exception $e) {
            throw new NotificationNotFound(sprintf('Notification not found at "%s"', $filePath));
        }

        $pattern = '/^\s*---\s*$/m';
        $parts   = preg_split($pattern, PHP_EOL . ltrim($content));

        if (count($parts) < 3) {
            throw new CouldNotParseNotificationException(
                sprintf(
                    'Email notification "%s" does not contain any needed meta information',
                    $notification->getFileName()
                )
            );
        }

        $configuration = Yaml::parse(trim($parts[1]));
        foreach ($configuration as $key => $value) {
            if (property_exists($notification, $key)) {
                $notification->{$key} = $value;
            } else {
                $notification->parameterBag->add($key, $value);
            }
        }

        $body = trim(implode(PHP_EOL . '---' . PHP_EOL, array_slice($parts, 2)));

        $notification->body = $body;

        return $notification;
    }

    /**
     * EmailNotification constructor.
     */
    public function __construct()
    {
        $this->parameterBag = new ParameterBag();

        parent::__construct();
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['fromName', 'fromEmail', 'name', 'fileName'], 'required'],
        ];
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function __get($name)
    {
        return $this->parameterBag->get($name);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return $this->parameterBag->has($name);
    }


    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return EmailNotification
     */
    public function setName(string $name): EmailNotification
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     *
     * @return EmailNotification
     */
    public function setFileName(string $fileName): EmailNotification
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $basename  = strtolower(pathinfo($fileName, PATHINFO_FILENAME));

        if (!in_array($extension, self::ALLOWED_FILE_EXTENSIONS, true)) {
            $fileName = $basename . '.' . self::DEFAULT_FILE_EXTENSION;
        }

        $this->fileName = $fileName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return EmailNotification
     */
    public function setDescription(string $description): EmailNotification
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getFromName(): string
    {
        return $this->fromName;
    }

    /**
     * @param string $fromName
     *
     * @return EmailNotification
     */
    public function setFromName(string $fromName): EmailNotification
    {
        $this->fromName = $fromName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFromEmail(): string
    {
        return $this->fromEmail;
    }

    /**
     * @param string $fromEmail
     *
     * @return EmailNotification
     */
    public function setFromEmail(string $fromEmail): EmailNotification
    {
        $this->fromEmail = $fromEmail;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getReplyTo()
    {
        return $this->replyTo;
    }

    /**
     * @param string $replyTo
     *
     * @return EmailNotification
     */
    public function setReplyTo(string $replyTo): EmailNotification
    {
        $this->replyTo = $replyTo;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCc()
    {
        return $this->cc ?: null;
    }

    /**
     * @param string $cc
     *
     * @return EmailNotification
     */
    public function setCc(string $cc = null): EmailNotification
    {
        $this->cc = $cc;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBcc()
    {
        return $this->bcc ?: null;
    }

    /**
     * @param string $bcc
     *
     * @return EmailNotification
     */
    public function setBcc(string $bcc = null): EmailNotification
    {
        $this->bcc = $bcc;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     *
     * @return EmailNotification
     */
    public function setSubject(string $subject): EmailNotification
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body ?? '';
    }

    /**
     * @param string $body
     *
     * @return EmailNotification
     */
    public function setBody(string $body): EmailNotification
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIncludeAttachments(): bool
    {
        return (bool) $this->includeAttachments;
    }

    /**
     * @param bool $includeAttachments
     *
     * @return EmailNotification
     */
    public function setIncludeAttachments(bool $includeAttachments): EmailNotification
    {
        $this->includeAttachments = $includeAttachments;

        return $this;
    }

    /**
     * @param string $filePath
     *
     * @return bool|null
     */
    public function writeToFile(string $filePath)
    {
        $handle = fopen($filePath, 'wb');

        $content = '';
        $content .= '---' . PHP_EOL;
        $content .= Yaml::dump($this->serializeAttributes());
        $content .= '---' . PHP_EOL;
        $content .= $this->getBody() . PHP_EOL;

        $result = fwrite($handle, $content);
        fclose($handle);

        return $result;
    }

    /**
     * @return array
     */
    private function serializeAttributes(): array
    {
        return array_merge(
            [
                'name'               => $this->getName(),
                'description'        => $this->getDescription(),
                'fromName'           => $this->getFromName(),
                'fromEmail'          => $this->getFromEmail(),
                'replyTo'            => $this->getReplyTo(),
                'cc'                 => $this->getCc(),
                'bcc'                => $this->getBcc(),
                'subject'            => $this->getSubject(),
                'includeAttachments' => $this->isIncludeAttachments(),
            ],
            $this->parameterBag->toArray()
        );
    }
}
