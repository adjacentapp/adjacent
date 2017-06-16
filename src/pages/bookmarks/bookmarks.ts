import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams } from 'ionic-angular';
import { ShowCardPage } from '../card/show';
import { BookmarksService } from '../../services/bookmarks.service';

@IonicPage()
@Component({
  selector: 'page-bookmarks',
  templateUrl: 'bookmarks.html',
})
export class BookmarksPage {
	loading: boolean;
	items: any[];

	constructor(public navCtrl: NavController, public navParams: NavParams, private bookmarksService: BookmarksService) {
		this.loading = true;
		this.bookmarksService.getBookmarks()
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
