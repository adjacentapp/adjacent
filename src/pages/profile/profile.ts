import { Component } from '@angular/core';
import { NavController, NavParams } from 'ionic-angular';
import { Http } from '@angular/http'
import 'rxjs/add/operator/map';
import { ShowCardPage } from '../card/show';

@Component({
	selector: 'profile-page',
	templateUrl: 'profile.html'
})
export class ProfilePage {
	// @Input() user_id: string;
	loading: boolean;
	user_id: string;
	profile: {fir_name: string, las_name: string, photo_url: string, skills: string};
	contributions: Array<any>;

	constructor(public navCtrl: NavController, public navParams: NavParams, public http: Http) {
		this.loading = true;
		this.user_id = navParams.get('user_id') || '1';
		var base_url = "http://localhost/~salsaia/adjacent/api/v2/";
		var query = '?user_id=' + this.user_id;
		let url = base_url + 'get_profile.php' + query;
		
		this.http.get(url).map(res => res.json()).subscribe(data => {
			this.profile = data;
			var skills = data.skills.replace(/[.,'"!-]/gi,'').split(' ');
			var skill = '';
			for(let s of skills)
				skill += ' #' + s;
			this.profile.skills = skill;
			this.loading = false;
		});
	}

	itemTapped(event, item) {
		this.navCtrl.push(ShowCardPage, {
			item: item
		});
	}
}
