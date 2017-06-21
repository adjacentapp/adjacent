import { Component } from '@angular/core';
import { NavController, NavParams } from 'ionic-angular';
import 'rxjs/add/operator/map';
import { ShowCardPage } from '../card/show';
import { ProfileService } from '../../services/profile.service';

@Component({
	selector: 'profile-page',
	templateUrl: 'profile.html'
})
export class ProfilePage {
	// @Input() user_id: string;
	loading: boolean;
	user_id: string;
	myself: boolean = false;
	profile: {fir_name: string, las_name: string, photo_url: string, skills: string};
	contributions: Array<any>;

	constructor(public navCtrl: NavController, public navParams: NavParams, private profileService: ProfileService) {
		this.loading = true;
		this.user_id = navParams.get('user_id') || '1';
		if(this.user_id == '1') this.myself = true;

		this.profileService.getProfile(this.user_id)
			.subscribe(
				profile => this.profile = profile,
				error => console.log(<any>error),
				() => this.loading = false
			);
	}

	showCard(event, item) {
		this.navCtrl.push(ShowCardPage, {
			item: item
		});
	}

	showContributions(event) {
		alert('my contributions');
		// this.navCtrl.push(ShowContributionsPage);
	}

	editTapped() {
		alert('Edit');
	}
}
