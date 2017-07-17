import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams } from 'ionic-angular';
import { ShowCardPage } from '../card/show';
import { BookmarksProvider } from '../../providers/bookmarks/bookmarks';
import { AuthProvider } from '../../providers/auth/auth';

@IonicPage()
@Component({
  selector: 'page-bookmarks',
  templateUrl: 'bookmarks.html',
})
export class BookmarksPage {
	loading: boolean;
	items: any[];

	constructor(public navCtrl: NavController, public navParams: NavParams, private bookmarks: BookmarksProvider, private auth: AuthProvider) {
		this.loading = true;
		this.bookmarks.getBookmarks(this.auth.currentUser.id)
			.subscribe(
				items => this.items = items,
				error => console.log(<any>error),
				() => this.loading = false
			);
	}

	itemTapped(event, item) {
		this.navCtrl.push(ShowCardPage, {
			item: item
		});
	}

	sortTapped(){
		alert("Sort by: 'date', 'industry', 'distance', 'search'");
	}

	showCard(event, item) {
		this.navCtrl.push(ShowCardPage, {
			item: item
		});
	}

	ionViewDidLoad() {
		console.log('ionViewDidLoad BookmarksPage');
	}

}
