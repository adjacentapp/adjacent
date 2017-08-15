import { Component } from '@angular/core';
import { NavController, NavParams, AlertController, LoadingController, Loading } from 'ionic-angular';
import { Camera, CameraOptions } from '@ionic-native/camera';
import 'rxjs/add/operator/map';
import { ShowCardPage } from '../card/show';
import { ProfileProvider, Profile } from '../../providers/profile/profile';
import { AuthProvider } from '../../providers/auth/auth';
import * as globs from '../../app/globals'

@Component({
	selector: 'edit-profile-page',
	templateUrl: 'edit.html'
})
export class EditProfilePage {
	loading: Loading;
	profile: Profile;
	skills = globs.SKILLS;

	constructor(
		public navCtrl: NavController, 
		public navParams: NavParams, 
		private profileProvider: ProfileProvider, 
		private auth: AuthProvider, 
		private loadingCtrl: LoadingController, 
		private alertCtrl: AlertController,
		private camera: Camera
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

	public updateNamesByIds(){
		this.profile.skill_names = globs.SKILLS
	            .filter(item => this.profile.skill_ids.indexOf(item.id) >= 0)
	            .map(item => item.name);
	    console.log(this.profile.skill_names);
	}


	uploadPhoto(){
		// this.camera.getPicutre().then((imageData) => {
		//  // imageData is either a base64 encoded string or a file URI
		//  // If it's base64:
		//  let base64Image = 'data:image/jpeg;base64,' + imageData;
		// }, (err) => {
		//  // Handle error
		// });
		// https://ionicframework.com/docs/native/camera/
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
