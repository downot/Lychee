<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace Tests\Feature_v2;

use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Session;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class PasswordBypassLoginTest extends BaseApiWithDataTest
{
	public function testBypassUserHasAlbumsUnlockedOnLogin(): void
	{
		// 1. Create a user with bypass permission
		$bypassUser = $this->createUserWithPermission('bypass_login_user', 'password', true);
		$bypassUser->grants_password_bypass = true;
		$bypassUser->save();

		// 2. Create an album with password
		$album = $this->createAlbum($this->admin, 'Locked Login Album');
		$this->setPassword($album, 'secret');

		// 3. Login
		$response = $this->postJson('/api/v2/Auth::login', [
			'username' => 'bypass_login_user',
			'password' => 'password',
		]);
		$response->assertNoContent();

		// 4. Assert that the album is in the unlocked session
		// This fails currently because the session is not populated
		$unlocked = Session::get(AlbumPolicy::UNLOCKED_ALBUMS_SESSION_KEY, []);
		$this->assertContains($album->id, $unlocked, 'Album ID should be in unlocked session after login for bypass user');
	}

	private function setPassword($album, $password)
	{
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
