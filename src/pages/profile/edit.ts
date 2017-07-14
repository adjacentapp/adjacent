import { Component } from '@angular/core';
import { NavController, NavParams, AlertController, LoadingController, Loading } from 'ionic-angular';
import 'rxjs/add/operator/map';
import { ShowCardPage } from '../card/show';
import { ProfileProvider } from '../../providers/profile/profile';
import { AuthProvider } from '../../providers/auth/auth';
import * as globs from '../../app/globals'

@Component({
	selector: 'edit-profile-page',
	templateUrl: 'edit.html'
})
export class EditProfilePage {
	loading: Loading;
	profile: {fir_name: string, las_name: string, photo_url: string, bio: string, skills: string};
	skills = globs.SKILLS;

	constructor(
		public navCtrl: NavController, 
		public navParams: NavParams, 
		private profileProvider: ProfileProvider, 
		private auth: AuthProvider, 
		private loadingCtrl: LoadingController, 
		private alertCtrl: AlertController
	) {
		this.profile = navParams.get('profile');
	}

	saveProfile() {
		this.showLoading();
		this.profileProvider.updateProfile(this.profile).subscribe(profile => {
			this.navCtrl.pop();
		},
			error => {
				this.showError(error);
			}
		);
	}


	uploadPhoto(){
		let alert = this.alertCtrl.create({
		  title: 'Under Construction',
		  subTitle: "Photo uploading is not yet supported.",
		  buttons: ['OK']
		});
		alert.present(prompt);
	}

	showLoading() {
	  this.loading = this.loadingCtrl.create({
	    content: 'Please wait...',
	    dismissOnPageChange: true
	  });
	  this.loading.present();
	}

	showError(text) {
	  if(this.loading)
	    this.loading.dismiss();

	  let alert = this.alertCtrl.create({
	    title: 'Oh no!',
	    // subTitle: text,
	    subTitle: "Something went wrong. Please try again.",
	    buttons: ['OK']
	  });
	  alert.present(prompt);
	}
}
