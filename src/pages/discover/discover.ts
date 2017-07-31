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
		this.navCtrl.push(ShowCardPage, {
			item: item
		});
	}

	slideChanged() {
		let currentIndex = this.slides.getActiveIndex();
		console.log('Current index is', currentIndex);
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

	doFollow(e, item){
		e.stopPropagation();
		let data = {
		  user_id: this.auth.currentUser.id,
		  card_id: item.id,
		  new_entry: !item.following
		};
		this.card.follow(data).subscribe(
			success => console.log(success),
			error => this.errorMessage = <any>error
		);
		// dangerous optimism!
		item.following = !item.following;
	}
}
