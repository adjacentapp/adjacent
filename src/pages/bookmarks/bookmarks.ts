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
	filters: any = [];
	reachedEnd: boolean = false;

	constructor(public navCtrl: NavController, public navParams: NavParams, private bookmarks: BookmarksProvider, private auth: AuthProvider) {
		this.loading = true;
		this.bookmarks.getBookmarks(this.auth.currentUser.id, 0)
			.subscribe(
				items => {
					this.items = items;
					this.reachedEnd = items.length < 10;
          console.log(this.items);
				},
				error => console.log(<any>error),
				() => this.loading = false
			);
	}

	sortTapped(){
		alert("Sort by: 'date', 'industry', 'distance', 'search'");
	}

	showCard(event, item) {
		this.navCtrl.push(ShowCardPage, {
			item: item
		});
	}

	doInfinite (): Promise<any> {
	  return new Promise((resolve) => {
		setTimeout(() => {
		  let offset = this.items.length;
		  this.bookmarks.getBookmarks(this.auth.currentUser.id, offset)
		  	.subscribe(
		  		items => {
		  			this.items = this.items.concat(items);
		  			this.reachedEnd = items.length < 10;
		  			resolve();
		  		},
		  		error => console.log(<any>error)
		  	);
	    }, 500);
	  });
	}

}
