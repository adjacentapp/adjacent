import { Component, ViewChild } from '@angular/core';
import { NavController, NavParams, MenuController, Slides } from 'ionic-angular';
import { ShowCardPage } from '../../pages/card/show';
import { CardProvider, Card } from '../../providers/card/card';
import { AuthProvider } from '../../providers/auth/auth';

@Component({
	selector: 'discover-page',
	templateUrl: 'discover.html'
})
export class DiscoverPage {
	@ViewChild(Slides) slides: Slides;
	items: Card[];
	loading: boolean;
	reachedEnd: boolean = false;
	dealing: boolean = false;

	errorMessage: string;

	username = '';
    email = '';

	constructor(public navCtrl: NavController, public navParams: NavParams, public menuCtrl: MenuController, private card: CardProvider, private auth: AuthProvider) {
		this.loading = true;
		this.getDeck();
	}

	getDeck() {
		this.card.getDeck(this.auth.currentUser.id, 0)
			.subscribe(
				items => this.items = items,
				error => {
					console.log('trying to get_cards again');
					this.getDeck();
					this.errorMessage = <any>error;
				},
				() => this.loading = false
			);
	}

	openRightMenu() {
	   this.menuCtrl.open('right');
	 }

	showCard(event, item) {
		let handleDelete = (_params) => {
		  return new Promise((resolve, reject) => {
	    	this.items = this.items.filter(item => item.id !== _params.remove_id);
		    resolve();
		  });
		}
		let handleUpdate = (updated) => {
		  return new Promise((resolve, reject) => {
		  	for (let i=0; i<this.items.length; i++)
		  		if(this.items[i].id == updated.id)
		  			this.items[i] = {...updated};
		  	resolve();
		  });
		}
		this.navCtrl.push(ShowCardPage, {
			item: item,
			deleteCallback: handleDelete,
			updateCallback: handleUpdate
		});
	}

	slideChanged() {
		let currentIndex = this.slides.getActiveIndex();
		if(!this.reachedEnd && !this.dealing && currentIndex >= this.items.length - 2) // deal more cards when 2 away from end
			this.dealCards();
	}

	dealCards() {
		this.dealing = true;
		let offset = this.items.length;
		this.card.getDeck(this.auth.currentUser.id, offset)
			.subscribe(
				items => {
					this.items = this.items.concat(items)
					if(!items.length){
						this.reachedEnd = true;
					}
				},
				error => this.errorMessage = <any>error,
				() => {
					this.dealing = false;
				}
			);
	}

	doRefresh (e) {
	  this.card.getDeck(this.auth.currentUser.id, 0)
	  	.subscribe(
	  		items => {
	  			this.items = items;
	  			this.slides.slideTo(1, 400);
	  		},
	  		error => this.errorMessage = <any>error,
	  		() => e.complete()
	  	);
	}
}
