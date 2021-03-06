<?php

/**
 * User: Warlof Tutsimo <loic.leuilliot@gmail.com>
 * Date: 08/08/2016
 * Time: 17:07
 */

namespace Warlof\Seat\Slackbot\Tests;

use Orchestra\Testbench\TestCase;
use Warlof\Seat\Slackbot\Repositories\SlackApi;

class SlackApiTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();

        $token = getenv('slack_token');

        app()->singleton(SlackApi::class, function() use ($token){
            return new SlackApi($token);
        });
    }

    public function testMemberChannels()
    {
        $slackUserId = "U1Z8TCZAT";
        $slackChannelsId = [
            'C3GPPCQB0',
            'G1Z9267L1',
            'G1ZUXJZSL'
        ];
        $apiResponse = app(SlackApi::class)->getUserConversations($slackUserId);

        $this->assertEquals($slackChannelsId, $apiResponse);
    }

    public function testMemberPublicChannel()
    {
        $slackUserId = "U1Z8TCZAT";
        $slackChannelsId = ["C3GPPCQB0"];
        $apiResponse = app(SlackApi::class)->getUserConversations($slackUserId, ['public_channel']);

        $this->assertEquals($slackChannelsId, $apiResponse);
    }

    public function testMemberPrivateChannel()
    {
        $slackUserId = "U1Z8TCZAT";
        $slackChannelsId = ["G1Z9267L1", "G1ZUXJZSL"];
        $apiResponse = app(SlackApi::class)->getUserConversations($slackUserId, ['private_channel']);

        $this->assertEquals($apiResponse, $slackChannelsId);
    }

    public function testInfoPublicChannel()
    {
        $slackChannelId = "C3GPPCQB0";
        $apiResponse = app(SlackApi::class)->getConversationInfo($slackChannelId);
        $artifact = $this->getArtifactPath("public_info.json");

        $this->assertJsonStringEqualsJsonFile($artifact,
            json_encode($apiResponse, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public function testInfoPrivateChannel()
    {
        $slackChannelId = "G1ZUXJZSL";
        $apiResponse = app(SlackApi::class)->getConversationInfo($slackChannelId);
        $artifact = $this->getArtifactPath("private_info.json");

        $this->assertJsonStringEqualsJsonFile($artifact,
            json_encode($apiResponse, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /**
     * @expectedException Warlof\Seat\Slackbot\Exceptions\SlackConversationException
     */
    public function testInfoChannelException()
    {
        $slackChannelId = "C2Z4D897";

        app(SlackApi::class)->getConversationInfo($slackChannelId);
    }

    /**
     * @expectedException Warlof\Seat\Slackbot\Exceptions\SlackConversationException
     */
    public function testInfoGroupException()
    {
        $slackChannelId = "G2Z4D897";

        app(SlackApi::class)->getConversationInfo($slackChannelId);
    }

    public function testInvitePublicChannel()
    {
        $slackUserId = "U1Z9QVCJW";
        $slackChannelId = "C1Z920QKC";

        $this->assertTrue(app(SlackApi::class)->inviteIntoConversation($slackUserId, $slackChannelId));
    }

    public function testInvitePrivateChannel()
    {
        $slackUserId = "U1Z9QVCJW";
        $slackChannelId = "G1Z9267L1";

        $this->assertTrue(app(SlackApi::class)->inviteIntoConversation($slackUserId, $slackChannelId));
    }

    public function testInvitePublicChannelException()
    {
        $slackUserId = "U1Z9LT9NP";
        $slackChannelId = "C1Z920QKC";

        $this->assertFalse(app(SlackApi::class)->inviteIntoConversation($slackUserId, $slackChannelId));
    }

    public function testInvitePrivateChannelException()
    {
        $slackUserId = "U1Z9LT9NP";
        $slackChannelId = "G1Z9267L1";

        $this->assertFalse(app(SlackApi::class)->inviteIntoConversation($slackUserId, $slackChannelId));
    }

    public function testKickPublicChannel()
    {
        $slackUserId = "U1Z9QVCJW";
        $slackChannelId = "C1Z920QKC";

        $this->assertTrue(app(SlackApi::class)->kickFromConversion($slackUserId, $slackChannelId));
    }

    public function testKickPrivateChannel()
    {
        $slackUserId = "U1Z9QVCJW";
        $slackChannelId = "G1Z9267L1";

        $this->assertTrue(app(SlackApi::class)->kickFromConversion($slackUserId, $slackChannelId));
    }

    /**
     * @expectedException Warlof\Seat\Slackbot\Exceptions\SlackConversationException
     */
    public function testKickPublicChannelException()
    {
        $slackUserId = "U1Z8TCZA3";
        $slackChannelId = "G1Z920QKC";

        $this->assertFalse(app(SlackApi::class)->kickFromConversion($slackUserId, $slackChannelId));
    }

    public function testKickPrivateChannelException()
    {
        $slackUserId = "U1Z8TCZA3";
        $slackChannelId = "C1Z8J1BFY";

        $this->assertFalse(app(SlackApi::class)->kickFromConversion($slackUserId, $slackChannelId));
    }

    public function testPublicChannels()
    {
        $apiResponse = app(SlackApi::class)->getConversations(['public_channel']);
        $artifact = $this->getArtifactPath("public_channel.json");

        $this->assertJsonStringEqualsJsonFile($artifact,
            json_encode($apiResponse, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public function testPrivateChannels()
    {
        $apiResponse = app(SlackApi::class)->getConversations(['private_channel']);
        $artifact = $this->getArtifactPath("private_channel.json");

        $this->assertJsonStringEqualsJsonFile($artifact,
            json_encode($apiResponse, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public function testChannels()
    {
        $apiResponse = app(SlackApi::class)->getConversations();
        $artifact = $this->getArtifactPath("channel.json");

        $this->assertJsonStringEqualsJsonFile($artifact,
            json_encode($apiResponse, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /**
     * @expectedException Warlof\Seat\Slackbot\Exceptions\SlackConversationException
     */
    public function testChannelsException()
    {
        app(SlackApi::class)->getConversations(['test']);
    }

    /**
     * @expectedException Warlof\Seat\Slackbot\Exceptions\SlackConversationException
     */
    public function testChannelMembersException()
    {
        app(SlackApi::class)->getConversationMembers('C6464646');
    }

    public function testMembers()
    {
        $apiResponse = app(SlackApi::class)->getTeamMembers();
        $artifact = $this->getArtifactPath("member.json");

        $this->assertJsonStringEqualsJsonFile($artifact,
            json_encode($apiResponse, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /**
     * @expectedException Warlof\Seat\Slackbot\Exceptions\SlackUserException
     */
    public function testMembersException()
    {
        app(SlackApi::class)->getTeamMembers('test-exception');
    }

    public function testMemberInfo()
    {
        $apiResponse = app(SlackApi::class)->getUserInfo('U1Z9QVCJW');
        $artifact = $this->getArtifactPath('user_info.json');

        $this->assertJsonStringEqualsJsonFile($artifact,
            json_encode($apiResponse, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /**
     * @expectedException Warlof\Seat\Slackbot\Exceptions\SlackApiException
     */
    public function testMemberInfoException()
    {
        app(SlackApi::class)->getUserInfo('UZTEST');
    }

    public function testOwnerKick()
    {
        $testUser = 'U1Z8TCZAT';
        $this->assertFalse(app(SlackApi::class)->kickFromConversion($testUser, 'C1Z920QKD'));
    }

    /**
     * @expectedException Warlof\Seat\Slackbot\Exceptions\SlackApiException
     */
    public function testApiException()
    {
        $api = new SlackApi('');
        $api->getTeamMembers();
    }

    private function getArtifactPath($artifactName)
    {
        return dirname(__FILE__) . "/artifacts/" . $artifactName;
    }
}
