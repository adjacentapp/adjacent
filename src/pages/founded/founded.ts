import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams } from 'ionic-angular';
import { ShowCardPage } from '../card/show';
import { NewCardPage } from '../card/new';
import { CardProvider, Card } from '../../providers/card/card';
import { AuthProvider } from '../../providers/auth/auth';

@IonicPage()
@Component({
  selector: 'page-founded',
  templateUrl: 'founded.html',
})
export class FoundedPage {
	loading: boolean;
	items: Card[];
	reachedEnd: boolean = false;

	constructor(public navCtrl: NavController, public navParams: NavParams, private card: CardProvider, private auth: AuthProvider) {
		this.loading = true;
		this.card.getFounded(this.auth.currentUser.id, 0)
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
		let handleCallback = (_params) => {
		  return new Promise((resolve, reject) => {
	    	this.items = this.items.filter(item => item.id !== _params.remove_id);
		    resolve();
		  });
		}
		this.navCtrl.push(ShowCardPage, {
			item: item,
			deleteCallback: handleCallback
		});
	}

	goToNewCard(event) {
		this.navCtrl.push(NewCardPage);
	}

	doInfinite (): Promise<any> {
	  return new Promise((resolve) => {
		setTimeout(() => {
		  let offset = this.items.length;
		  this.card.getFounded(this.auth.currentUser.id, offset)
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
