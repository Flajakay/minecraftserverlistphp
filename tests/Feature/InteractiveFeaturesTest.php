<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Server;
use App\Models\User;
use App\Models\Comment;
use App\Models\BlogPost;
use App\Models\Setting;
use App\Core\Features\Comments;
use App\Core\System\Database;

class InteractiveFeaturesTest extends TestCase
{
    private $user1Id;
    private $user2Id;
    private $adminUserId;
    private $serverId;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->user1Id = User::create([
            'username' => 'commenter1',
            'password' => password_hash('secret123', PASSWORD_ARGON2ID),
            'email' => 'c1@example.com',
            'name' => 'Commenter One',
            'type' => 0,
            'active' => 1
        ]);

        $this->user2Id = User::create([
            'username' => 'commenter2',
            'password' => password_hash('secret123', PASSWORD_ARGON2ID),
            'email' => 'c2@example.com',
            'name' => 'Commenter Two',
            'type' => 0,
            'active' => 1
        ]);

        $this->adminUserId = User::create([
            'username' => 'sysadmin',
            'password' => password_hash('secret123', PASSWORD_ARGON2ID),
            'email' => 'sysadmin@example.com',
            'name' => 'System Admin',
            'type' => 2,
            'active' => 1
        ]);

        // Create test server owned by commenter1
        $this->serverId = Server::create([
            'user_id' => $this->user1Id,
            'category_id' => 1,
            'address' => 'interactive.example.com',
            'port' => 25565,
            'name' => 'Interactive Test Server'
        ]);
    }

    /**
     * Test comment censorship with banned words.
     */
    public function testCommentCensorship()
    {
        // 1. Configure banned words in the database settings
        Setting::update([
            'banned_words' => 'spam, illegal, hack'
        ]);

        // Clear static cache in Setting class so it fetches fresh settings from the database
        Setting::clearCache();

        // 2. Add a clean comment (should succeed)
        $cleanResult = Comments::createComment($this->serverId, $this->user2Id, 'This is a fantastic server!');
        $this->assertTrue($cleanResult['success']);
        $this->assertNotNull(Comment::find($cleanResult['commentId']));

        // 3. Add an inappropriate comment (should fail)
        $inappropriateResult = Comments::createComment($this->serverId, $this->user2Id, 'Come play here, it has illegal hacks and spam!');
        $this->assertFalse($inappropriateResult['success']);
        $this->assertEquals('Comment contains inappropriate language', $inappropriateResult['error']);
        $this->assertArrayNotHasKey('commentId', $inappropriateResult);
    }

    /**
     * Test comment deletion permission matrix.
     */
    public function testCommentDeletionPermissions()
    {
        $commentId = Comment::create([
            'server_id' => $this->serverId,
            'user_id' => $this->user2Id,
            'comment' => 'This is a valid comment'
        ]);

        $comment = Comment::find($commentId);
        $server = Server::find($this->serverId);

        $author = User::find($this->user2Id);
        $serverOwner = User::find($this->user1Id);
        $otherUser = User::find($this->adminUserId); // Has admin type 2
        $randomUser = User::find($this->user1Id); // Let's make a mock user to test denied access

        // Author can delete
        $this->assertTrue(Comments::canDeleteComment($author, $comment, $server));

        // Server owner can moderate/delete
        $this->assertTrue(Comments::canDeleteComment($serverOwner, $comment, $server));

        // Admin can delete
        $this->assertTrue(Comments::canDeleteComment($otherUser, $comment, $server));

        // Delete using API
        $deleteResult = Comments::deleteComment($commentId, $this->user2Id);
        $this->assertTrue($deleteResult['success']);
        $this->assertFalse(Comment::find($commentId));
    }

    /**
     * Test blog post creation and server retrieval.
     */
    public function testBlogPostManagement()
    {
        $blogData = [
            'server_id' => $this->serverId,
            'user_id' => $this->user1Id,
            'title' => 'Big Server Update!',
            'content' => 'We updated our servers to support 1.20.4 successfully!'
        ];

        $postId = BlogPost::create($blogData);
        $this->assertGreaterThan(0, $postId);

        // Find blog post
        $post = BlogPost::find($postId);
        $this->assertNotNull($post);
        $this->assertEquals('Big Server Update!', $post->title);
        $this->assertEquals($this->serverId, $post->server_id);

        // Get server posts
        $posts = BlogPost::getServerPosts($this->serverId);
        $this->assertCount(1, $posts);
        $this->assertEquals('Big Server Update!', $posts[0]->title);
    }

    /**
     * Test comment cascades when a server or user is deleted.
     */
    public function testCommentCascadesOnServerAndUserDeletion()
    {
        // 1. Create a comment
        $commentId = Comment::create([
            'server_id' => $this->serverId,
            'user_id' => $this->user2Id,
            'comment' => 'This is a comment for deletion test'
        ]);

        $this->assertNotNull(Comment::find($commentId));

        // 2. Delete the server
        Server::delete($this->serverId);

        // Comment should be cascade deleted by Server::delete()
        $this->assertFalse(Comment::find($commentId));

        // Create a new server for user deletion test
        $newServerId = Server::create([
            'user_id' => $this->user1Id,
            'category_id' => 1,
            'address' => 'new.example.com',
            'port' => 25565,
            'name' => 'New Server'
        ]);

        $newCommentId = Comment::create([
            'server_id' => $newServerId,
            'user_id' => $this->user2Id,
            'comment' => 'This is a comment by user 2'
        ]);

        $this->assertNotNull(Comment::find($newCommentId));

        // Delete user2
        User::delete($this->user2Id);

        // Comment should be cascade deleted by User::delete()
        $this->assertFalse(Comment::find($newCommentId));
    }
}
