import { Component } from '@angular/core';
import { NavController, NavParams } from 'ionic-angular';
import 'rxjs/add/operator/map';
// import { ShowCardPage } from '../card/show';
import { EditProfilePage } from '../profile/edit';
import { ProfileProvider, Profile } from '../../providers/profile/profile';
import { AuthProvider } from '../../providers/auth/auth';
import * as globs from '../../app/globals'
import { ShowMessagePage } from '../../pages/messages/show';

@Component({
	selector: 'profile-page',
	templateUrl: 'profile.html'
})
export class ProfilePage {
	// @Input() user_id: string;
	loading: boolean;
	user_id: number;
	myself: boolean = false;
	profile: Profile;
	contributions: Array<any>;

	private networks = globs.NETWORKS;
	private networkCount = Math.round( Math.random()*10 );

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
	    item: {
	      id: null,
	      card_id: 0, // card_id 0 is normal peer-peer convo
	      messages: [],
	      other: {
	        id: this.profile.user.id,
	        fir_name: this.profile.user.fir_name
	      }
	    }
	  }); 
	}

	showContributions(event) {
		alert('my contributions');
		// this.navCtrl.push(ShowContributionsPage);
	}

	editTapped() {
		this.navCtrl.push(EditProfilePage, {
			profile: this.profile
		});
	}
}
