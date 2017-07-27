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
	offset: number = 0;
	limit: number = 10;
	filters: any = [];
	reachedEnd: boolean = false;

	constructor(public navCtrl: NavController, public navParams: NavParams, private bookmarks: BookmarksProvider, private auth: AuthProvider) {
		this.loading = true;
		this.bookmarks.getBookmarks(this.auth.currentUser.id, this.offset, this.limit, this.filters)
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

	doInfinite(e){
	  if(this.reachedEnd){
	  	e.complete();
	  	return;
	  }

	  this.offset += this.limit;
	  this.bookmarks.getBookmarks(this.auth.currentUser.id, this.offset, this.limit, this.filters)
	  	.subscribe(
	  		items => {
	  			this.items = this.items.concat(items);
	  			if(items.length < this.limit){
	  				this.reachedEnd = true;
	  			}
	  		},
	  		error => console.log(<any>error),
	  		() => {
	  			this.loading = false;
	  			e.complete();
	  		}
	  	);
	}

}
