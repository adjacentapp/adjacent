import { Component } from '@angular/core';
import { NavController, NavParams } from 'ionic-angular';
import 'rxjs/add/operator/map';
import { EditProfilePage } from '../profile/edit';
import { ProfileProvider, Profile } from '../../providers/profile/profile';
import { AuthProvider } from '../../providers/auth/auth';
import * as globs from '../../app/globals'
import { ShowMessagePage } from '../../pages/messages/show';
import { Conversation } from '../../providers/messages/messages';

@Component({
	selector: 'profile-page',
	templateUrl: 'profile.html'
})
export class ProfilePage {
	loading: boolean;
	user_id: number;
	myself: boolean = false;
	profile: Profile;
	skills = globs.SKILLS;

	constructor(public navCtrl: NavController, public navParams: NavParams, private profileProvider: ProfileProvider, private auth: AuthProvider) {
		this.loading = true;
		this.user_id = navParams.get('user_id') || this.auth.currentUser.id;
		if(this.user_id == this.auth.currentUser.id) this.myself = true;

		this.profileProvider.getProfile(this.user_id, this.auth.currentUser.id)
			.subscribe(
				profile => this.profile = profile,
				error => console.log(<any>error),
				() => this.loading = false
			);
	}

	goToMessage(){
	  this.navCtrl.push(ShowMessagePage, {
	    item: new Conversation(
        null, // id
        {...this.profile.user}, // other
        null, // card
        [], // messages
        null, // timstamp
        null // unread
      )
	  });
	}

	editTapped() {
		let handleUpdate = (_params) => {
		  return new Promise((resolve, reject) => {
		    this.profile = {..._params};
		    resolve();
		  });
		}
		this.navCtrl.push(EditProfilePage, {
			profile: this.profile,
			updateCallback: handleUpdate
		});
	}
}
