import { Component } from '@angular/core';
import { NavController, NavParams } from 'ionic-angular';
import { Http } from '@angular/http'
import 'rxjs/add/operator/map';
import { ShowCardPage } from '../card/show';

@Component({
	selector: 'discover-page',
	templateUrl: 'discover.html'
})
export class DiscoverPage {
	industries: string[];
	items: Array<{industry: string, pitch: string, distance: string}>;
	url: string;
	loading: boolean;

	constructor(public navCtrl: NavController, public navParams: NavParams, public http: Http){//, public loadingCtrl: LoadingController) {
		this.loading = true;
		this.industries = ['Agriculture', 'Art', 'Architecture', 'Business', 'Computer Science', 'Design', 'Education', 'Engineering', 'Entrepreneurship', 'Finance', 'Government', 'Healthcare', 'Humanities', 'Journalism', 'Languages', 'Law', 'Marketing', 'Math', 'Music', 'Performing Arts', 'Science', 'Social Impact', 'Sports', 'Writing'];

		var DEV = true;
		var prod_url = "http://adjacent.wuex59etyj.us-west-2.elasticbeanstalk.com/api/v1/";
		var dev_url = "http://localhost/~salsaia/adjacent/api/v2/";
		var base_url = DEV ? dev_url : prod_url;
		var query = '?user_id=1'
		this.url = base_url + 'get_cards.php' + query;

		this.http.get(this.url).map(res => res.json()).subscribe(data => {
			this.items = data;
			console.log(data);
			this.items.unshift({
				industry: 'test',
				pitch: 'test',
				distance: ''
			});
			this.loading = false;
		});
	}

	itemTapped(event, item) {
		this.navCtrl.push(ShowCardPage, {
			item: item
		});
	}

	followTapped(event, item) {
		event.stopPropagation();
		alert('follow!');
	}

	presentLoadingDefault() {
	  // let loading = this.loadingCtrl.create({
	  //   content: 'Please wait...'
	  // });

	  // loading.present();

	  // setTimeout(() => {
	  //   loading.dismiss();
	  // }, 5000);
	}
}
