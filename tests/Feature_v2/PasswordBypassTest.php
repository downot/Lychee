<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace Tests\Feature_v2;

use App\Models\Configs;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class PasswordBypassTest extends BaseApiWithDataTest
{
	/**
	 * Test that a user with grants_password_bypass can access a password protected album
	 * without providing a password.
	 */
	public function testBypassUserCanAccessPasswordProtectedAlbum(): void
	{
		// 1. Create a user with bypass permission
		$bypassUser = $this->createUserWithPermission('bypass_user', 'password', true);
		$bypassUser->grants_password_bypass = true;
		$bypassUser->save();

		// 2. Create an album with password
		$album = $this->createAlbum($this->admin, 'Locked Album');
		$this->setPassword($album, 'secret');

		// 3. Authenticate as bypass user and try to get the album
		// We expect OK (200) because the middleware should unlock it automatically
		$response = $this->actingAs($bypassUser)->getJsonWithData('Album', ['album_id' => $album->id]);
		
		$response->assertOk();
		$response->assertJson([
			'config' => [
				'is_password_protected' => false, // SHould be reported as NOT protected for this user
				'is_accessible' => true,
			]
		]);
	}

	/**
	 * Test that a normal user cannot access without password.
	 */
	public function testNormalUserCannotAccessPasswordProtectedAlbum(): void
	{
		$normalUser = $this->createUserWithPermission('normal_user', 'password', true);
		
		$album = $this->createAlbum($this->admin, 'Locked Album 2');
		$this->setPassword($album, 'secret');

		$response = $this->actingAs($normalUser)->getJsonWithData('Album', ['album_id' => $album->id]);
		
		// Expect 401 or 403 because password is required
		$response->assertStatus(401); 
	}
	
	private function setPassword($album, $password)
	{
		// Helper to set password on album (simulating the request or direct DB)
		// We can use the SetProtectionPolicy action or just direct DB for test speed
		// Direct DB:
		// We need to set it in access_permissions
		// But BaseApiWithDataTest might have helpers. 
		// Let's use the API to be safe if possible, or direct DB manipulation.
		
		// Using the API to set protection policy requires admin.
		// Let's assume $album is BaseAlbum model.
		// We need to create an AccessPermission for it.
		// Actually AbstractTestCase probably has helpers or we can do it manually.
		
		\App\Models\AccessPermission::factory()->create([
			'base_album_id' => $album->id,
			'password' => \Illuminate\Support\Facades\Hash::make($password),
			'user_id' => null
		]);
	}
	
	private function createAlbum($user, $title)
	{
		return \App\Models\Album::factory()->create([
			'owner_id' => $user->id,
			'title' => $title,
		]);
	}
	
	private function createUserWithPermission($username, $password, $can_upload) {
		return \App\Models\User::factory()->create([
			'username' => $username,
			'password' => \Illuminate\Support\Facades\Hash::make($password),
			'may_upload' => $can_upload
		]);
	}
}
