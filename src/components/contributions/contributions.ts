import { Component, Input } from '@angular/core';
import { NavController, NavParams } from 'ionic-angular';
import { ShowCardPage } from '../../pages/card/show';
import { Card } from '../../providers/card/card';
import { Comment } from '../../providers/wall/wall';
import { BookmarksProvider } from '../../providers/bookmarks/bookmarks';

@Component({
  selector: 'contributions',
  templateUrl: 'contributions.html'
})
export class ContributionsComponent {
  @Input() user_id: number;
  items: Card[];
  loading: boolean;
  reachedEnd: boolean = false;
  limit: number = 10;
  
  constructor(public navCtrl: NavController, public navParams: NavParams, private bookmarks: BookmarksProvider) {}

  ngOnInit() {
  	this.loading = true;
  	this.bookmarks.getContributions(this.user_id, 0)
  		.subscribe(
  			items => {
  				this.items = items;
  				this.reachedEnd = items.length < this.limit;
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
  	    this.bookmarks.getContributions(this.user_id, offset)
  	  	  .subscribe(
  	  		items => {
  	  			this.items = this.items.concat(items);
  	  			this.reachedEnd = items.length < this.limit;
  	  			resolve();
  	  		},
  	  		error => console.log(<any>error)
  	  	  );
      }, 500);
    });
  }

}
