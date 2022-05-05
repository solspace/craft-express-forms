<?php

namespace Solspace\ExpressForms\models;

use craft\base\Model;
use PhpParser\Node\Param;
use Solspace\ExpressForms\exceptions\EmailNotifications\CouldNotParseNotificationException;
use Solspace\ExpressForms\exceptions\EmailNotifications\EmailNotificationsException;
use Solspace\ExpressForms\exceptions\EmailNotifications\NotificationNotFound;
use Solspace\ExpressForms\exceptions\EmailNotifications\NotificationTemplateFolderNotSetException;
use Solspace\ExpressForms\objects\ParameterBag;
use Symfony\Component\Yaml\Yaml;

class EmailNotification extends Model
{
    public const REQUIRED_METADATA = [
        'fromName',
        'fromEmail',
    ];

    public const ALLOWED_FILE_EXTENSIONS = ['twig', 'html'];
    public const DEFAULT_FILE_EXTENSION = 'twig';

    public ?string $name = null;
    public ?string $fromName = null;
    public ?string $fromEmail = null;
    public ?string $replyTo = null;
    public ?string $cc = null;
    public ?string $bcc = null;
    public ?string $subject = null;
    public ?string $body = null;
    public ?bool $includeAttachments = false;
    public ?string $fileName = null;
    private ?string $description = null;
    private ParameterBag $parameterBag;

    public function __construct()
    {
        $this->parameterBag = new ParameterBag();

        parent::__construct();
    }

    /**
     * @param string $name
     *
     * @return null|mixed
     */
    public function __get($name)
    {
        return $this->parameterBag->get($name);
    }

    /**
     * @param string $name
     */
    public function __isset($name): bool
    {
        return $this->parameterBag->has($name);
    }

    public static function create(
        string $directory,
        string $fileName = 'template',
        bool $overwrite = false
    ): self {
        if (!file_exists($directory) || !is_dir($directory)) {
            throw new NotificationTemplateFolderNotSetException('No email notification templates folder set');
        }

        $base = pathinfo($fileName, \PATHINFO_FILENAME);
        $ext = strtolower(pathinfo($fileName, \PATHINFO_EXTENSION));
        if (!\in_array($ext, ['twig', 'html'])) {
            $ext = 'twig';
        }

        $i = 0;
        do {
            if (0 === $i) {
                $fileName = "{$base}.{$ext}";
            } else {
                $fileName = $base."_{$i}.{$ext}";
            }

            $filePath = $directory.'/'.$fileName;
            ++$i;

            if (!file_exists($filePath) || $overwrite) {
                break;
            }
        } while ($i < 100);

        if ($i >= 100) {
            throw new EmailNotificationsException('Could not create a new template');
        }

        $notification = new self();
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
                <<<'DATA'
                    <p>The following submission came in on {{ dateCreated|date('l, F j, Y \\a\\t g:ia') }}.</p>

                    <ul>
                        {% for field in form.fields %}
                            <li>{{ field.label }}: {{ field.valueAsString }}</li>
                        {% endfor %}
                    </ul>
                    DATA
            )
        ;

        return $notification;
    }

    public static function fromFile(string $filePath = null): self
    {
        if (null === $filePath) {
            throw new EmailNotificationsException('No path specified');
        }

        $notification = new self();
        $notification->setFileName(pathinfo($filePath, \PATHINFO_BASENAME));

        try {
            $content = file_get_contents($filePath);
        } catch (\Exception $e) {
            throw new NotificationNotFound(sprintf('Notification not found at "%s"', $filePath));
        }

        $pattern = '/^\s*---\s*$/m';
        $parts = preg_split($pattern, \PHP_EOL.ltrim($content));

        if (\count($parts) < 3) {
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

        $body = trim(implode(\PHP_EOL.'---'.\PHP_EOL, \array_slice($parts, 2)));

        $notification->body = $body;

        return $notification;
    }

    public function rules(): array
    {
        return [
            [['fromName', 'fromEmail', 'name', 'fileName'], 'required'],
        ];
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): self
    {
        $extension = strtolower(pathinfo($fileName, \PATHINFO_EXTENSION));
        $basename = strtolower(pathinfo($fileName, \PATHINFO_FILENAME));

        if (!\in_array($extension, self::ALLOWED_FILE_EXTENSIONS, true)) {
            $fileName = $basename.'.'.self::DEFAULT_FILE_EXTENSION;
        }

        $this->fileName = $fileName;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getFromName(): string
    {
        return $this->fromName;
    }

    public function setFromName(string $fromName): self
    {
        $this->fromName = $fromName;

        return $this;
    }

    public function getFromEmail(): string
    {
        return $this->fromEmail;
    }

    public function setFromEmail(string $fromEmail): self
    {
        $this->fromEmail = $fromEmail;

        return $this;
    }

    public function getReplyTo(): ?string
    {
        return $this->replyTo;
    }

    public function setReplyTo(string $replyTo): self
    {
        $this->replyTo = $replyTo;

        return $this;
    }

    public function getCc(): ?string
    {
        return $this->cc ?: null;
    }

    public function setCc(?string $cc): self
    {
        $this->cc = $cc;

        return $this;
    }

    public function getBcc(): ?string
    {
        return $this->bcc ?: null;
    }

    public function setBcc(?string $bcc): self
    {
        $this->bcc = $bcc;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getBody(): string
    {
        return $this->body ?? '';
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function isIncludeAttachments(): bool
    {
        return (bool) $this->includeAttachments;
    }

    public function setIncludeAttachments(bool $includeAttachments): self
    {
        $this->includeAttachments = $includeAttachments;

        return $this;
    }

    public function writeToFile(string $filePath): ?bool
    {
        $handle = fopen($filePath, 'w');

        $content = '';
        $content .= '---'.\PHP_EOL;
        $content .= Yaml::dump($this->serializeAttributes());
        $content .= '---'.\PHP_EOL;
        $content .= $this->getBody().\PHP_EOL;

        $result = fwrite($handle, $content);
        fclose($handle);

        return $result;
    }

    private function serializeAttributes(): array
    {
        return array_merge(
            [
                'name' => $this->getName(),
                'description' => $this->getDescription(),
                'fromName' => $this->getFromName(),
                'fromEmail' => $this->getFromEmail(),
                'replyTo' => $this->getReplyTo(),
                'cc' => $this->getCc(),
                'bcc' => $this->getBcc(),
                'subject' => $this->getSubject(),
                'includeAttachments' => $this->isIncludeAttachments(),
            ],
            $this->parameterBag->toArray()
        );
    }
}
