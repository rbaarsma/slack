<?php

declare(strict_types=1);

namespace Nexy\Slack;

use GuzzleHttp\Client as Guzzle;

class Client
{
    /**
     * The Slack incoming webhook endpoint.
     *
     * @var string
     */
    private $endpoint;

    /**
     * The default channel to send messages to.
     *
     * @var string
     */
    private $channel;

    /**
     * The default username to send messages as.
     *
     * @var string
     */
    private $username;

    /**
     * The default icon to send messages with.
     *
     * @var string
     */
    private $icon;

    /**
     * Whether to link names like @regan or leave
     * them as plain text.
     *
     * @var bool
     */
    private $link_names = false;

    /**
     * Whether Slack should unfurl text-based URLs.
     *
     * @var bool
     */
    private $unfurl_links = false;

    /**
     * Whether Slack should unfurl media URLs.
     *
     * @var bool
     */
    private $unfurl_media = true;

    /**
     * Whether message text should be formatted with Slack's
     * Markdown-like language.
     *
     * @var bool
     */
    private $allow_markdown = true;

    /**
     * The attachment fields which should be formatted with
     * Slack's Markdown-like language.
     *
     * @var array
     */
    private $markdown_in_attachments = [];

    /**
     * The Guzzle HTTP client instance.
     *
     * @var \GuzzleHttp\Client
     */
    private $guzzle;

    /**
     * Instantiate a new Client.
     *
     * @param string $endpoint
     * @param array  $attributes
     */
    public function __construct($endpoint, array $attributes = [], Guzzle $guzzle = null)
    {
        $this->endpoint = $endpoint;

        if (isset($attributes['channel'])) {
            $this->setDefaultChannel($attributes['channel']);
        }

        if (isset($attributes['username'])) {
            $this->setDefaultUsername($attributes['username']);
        }

        if (isset($attributes['icon'])) {
            $this->setDefaultIcon($attributes['icon']);
        }

        if (isset($attributes['link_names'])) {
            $this->setLinkNames($attributes['link_names']);
        }

        if (isset($attributes['unfurl_links'])) {
            $this->setUnfurlLinks($attributes['unfurl_links']);
        }

        if (isset($attributes['unfurl_media'])) {
            $this->setUnfurlMedia($attributes['unfurl_media']);
        }

        if (isset($attributes['allow_markdown'])) {
            $this->setAllowMarkdown($attributes['allow_markdown']);
        }

        if (isset($attributes['markdown_in_attachments'])) {
            $this->setMarkdownInAttachments($attributes['markdown_in_attachments']);
        }

        $this->guzzle = $guzzle ?: new Guzzle();
    }

    /**
     * Pass any unhandled methods through to a new Message
     * instance.
     *
     * @param string $name      The name of the method
     * @param array  $arguments The method arguments
     *
     * @return \Nexy\Slack\Message
     */
    public function __call($name, $arguments)
    {
        return \call_user_func_array([$this->createMessage(), $name], $arguments);
    }

    /**
     * Get the Slack endpoint.
     *
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * Set the Slack endpoint.
     *
     * @param string $endpoint
     */
    public function setEndpoint($endpoint): void
    {
        $this->endpoint = $endpoint;
    }

    /**
     * Get the default channel messages will be created for.
     *
     * @return string
     */
    public function getDefaultChannel()
    {
        return $this->channel;
    }

    /**
     * Set the default channel messages will be created for.
     *
     * @param string $channel
     */
    public function setDefaultChannel($channel): void
    {
        $this->channel = $channel;
    }

    /**
     * Get the default username messages will be created for.
     *
     * @return string
     */
    public function getDefaultUsername()
    {
        return $this->username;
    }

    /**
     * Set the default username messages will be created for.
     *
     * @param string $username
     */
    public function setDefaultUsername($username): void
    {
        $this->username = $username;
    }

    /**
     * Get the default icon messages will be created with.
     *
     * @return string
     */
    public function getDefaultIcon()
    {
        return $this->icon;
    }

    /**
     * Set the default icon messages will be created with.
     *
     * @param string $icon
     */
    public function setDefaultIcon($icon): void
    {
        $this->icon = $icon;
    }

    /**
     * Get whether messages sent will have names (like @regan)
     * will be converted into links.
     *
     * @return bool
     */
    public function getLinkNames()
    {
        return $this->link_names;
    }

    /**
     * Set whether messages sent will have names (like @regan)
     * will be converted into links.
     *
     * @param bool $value
     */
    public function setLinkNames($value): void
    {
        $this->link_names = (bool) $value;
    }

    /**
     * Get whether text links should be unfurled.
     *
     * @return bool
     */
    public function getUnfurlLinks()
    {
        return $this->unfurl_links;
    }

    /**
     * Set whether text links should be unfurled.
     *
     * @param bool $value
     */
    public function setUnfurlLinks($value): void
    {
        $this->unfurl_links = (bool) $value;
    }

    /**
     * Get whether media links should be unfurled.
     *
     * @return bool
     */
    public function getUnfurlMedia()
    {
        return $this->unfurl_media;
    }

    /**
     * Set whether media links should be unfurled.
     *
     * @param bool $value
     */
    public function setUnfurlMedia($value): void
    {
        $this->unfurl_media = (bool) $value;
    }

    /**
     * Get whether message text should be formatted with
     * Slack's Markdown-like language.
     *
     * @return bool
     */
    public function getAllowMarkdown()
    {
        return $this->allow_markdown;
    }

    /**
     * Set whether message text should be formatted with
     * Slack's Markdown-like language.
     *
     * @param bool $value
     */
    public function setAllowMarkdown($value): void
    {
        $this->allow_markdown = (bool) $value;
    }

    /**
     * Get the attachment fields which should be formatted
     * in Slack's Markdown-like language.
     *
     * @return array
     */
    public function getMarkdownInAttachments()
    {
        return $this->markdown_in_attachments;
    }

    /**
     * Set the attachment fields which should be formatted
     * in Slack's Markdown-like language.
     *
     * @param array $fields
     */
    public function setMarkdownInAttachments(array $fields): void
    {
        $this->markdown_in_attachments = $fields;
    }

    /**
     * Create a new message with defaults.
     *
     * @return \Nexy\Slack\Message
     */
    public function createMessage()
    {
        $message = new Message($this);

        $message->setChannel($this->getDefaultChannel());

        $message->setUsername($this->getDefaultUsername());

        $message->setIcon($this->getDefaultIcon());

        $message->setAllowMarkdown($this->getAllowMarkdown());

        $message->setMarkdownInAttachments($this->getMarkdownInAttachments());

        return $message;
    }

    /**
     * Send a message.
     *
     * @param \Nexy\Slack\Message $message
     */
    public function sendMessage(Message $message): void
    {
        $payload = $this->preparePayload($message);

        $encoded = \json_encode($payload, JSON_UNESCAPED_UNICODE);

        if (false === $encoded) {
            throw new \RuntimeException(\sprintf('JSON encoding error %s: %s', \json_last_error(), \json_last_error_msg()));
        }

        $this->guzzle->post($this->endpoint, ['body' => $encoded]);
    }

    /**
     * Prepares the payload to be sent to the webhook.
     *
     * @param \Nexy\Slack\Message $message The message to send
     *
     * @return array
     */
    public function preparePayload(Message $message)
    {
        $payload = [
            'text' => $message->getText(),
            'channel' => $message->getChannel(),
            'username' => $message->getUsername(),
            'link_names' => $this->getLinkNames() ? 1 : 0,
            'unfurl_links' => $this->getUnfurlLinks(),
            'unfurl_media' => $this->getUnfurlMedia(),
            'mrkdwn' => $message->getAllowMarkdown(),
        ];

        if ($icon = $message->getIcon()) {
            $payload[$message->getIconType()] = $icon;
        }

        $payload['attachments'] = $this->getAttachmentsAsArrays($message);

        return $payload;
    }

    /**
     * Get the attachments in array form.
     *
     * @param \Nexy\Slack\Message $message
     *
     * @return array
     */
    private function getAttachmentsAsArrays(Message $message)
    {
        $attachments = [];

        foreach ($message->getAttachments() as $attachment) {
            $attachments[] = $attachment->toArray();
        }

        return $attachments;
    }
}
