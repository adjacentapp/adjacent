import { Component, Input } from '@angular/core';
import { NavController, NavParams } from 'ionic-angular';
import { ShowCardPage } from '../../pages/card/show';
import { BookmarksProvider } from '../../providers/bookmarks/bookmarks';
import { Card } from '../../providers/card/card';

@Component({
  selector: 'bookmarks',
  templateUrl: 'bookmarks.html'
})
export class BookmarksComponent {
  @Input() user_id: number;
  items: Card[];
  loading: boolean;
  reachedEnd: boolean = false;

  constructor(public navCtrl: NavController, public navParams: NavParams, private bookmarks: BookmarksProvider) {}

  ngOnInit() {
  	this.loading = true;
  	this.bookmarks.getBookmarks(this.user_id, 0)
  		.subscribe(
  			items => {
  				this.items = items;
  				this.reachedEnd = items.length < 10;
  			},
  			error => console.log(<any>error),
  			() => this.loading = false
  		);
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
  	    this.bookmarks.getBookmarks(this.user_id, offset)
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
