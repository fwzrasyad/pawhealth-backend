<?php

namespace App\Services;

use TaylanUnutmaz\AgoraTokenBuilder\RtcTokenBuilder;

class AgoraService
{
    private string $appId;
    private string $appCertificate;

    // Role constants
    const ROLE_PUBLISHER = RtcTokenBuilder::RolePublisher;
    const ROLE_SUBSCRIBER = RtcTokenBuilder::RoleSubscriber;

    public function __construct()
    {
        $this->appId = env('AGORA_APP_ID', '');
        $this->appCertificate = env('AGORA_APP_CERTIFICATE', '');
    }

    /**
     * Get the Agora App ID.
     */
    public function getAppId(): string
    {
        return $this->appId;
    }

    /**
     * Generate an Agora RTC token (006 format).
     *
     * @param  string  $channelName  The channel name for the video call.
     * @param  int     $uid          The user ID within the Agora channel.
     * @param  int     $role         ROLE_PUBLISHER (1) or ROLE_SUBSCRIBER (2).
     * @param  int     $expireSeconds  Token validity duration in seconds.
     * @return string  The generated Agora token.
     */
    public function generateToken(string $channelName, int $uid, int $role = self::ROLE_PUBLISHER, int $expireSeconds = 3600): string
    {
        $currentTimestamp = time();
        $privilegeExpiredTs = $currentTimestamp + $expireSeconds;

        return RtcTokenBuilder::buildTokenWithUid(
            $this->appId,
            $this->appCertificate,
            $channelName,
            $uid,
            $role,
            $privilegeExpiredTs
        );
    }
}
