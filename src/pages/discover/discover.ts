import { Component } from '@angular/core';
import { NavController, NavParams, MenuController } from 'ionic-angular';
import { ShowCardPage } from '../../pages/card/show';
import { CardService } from '../../services/card.service';

@Component({
	selector: 'discover-page',
	templateUrl: 'discover.html'
})
export class DiscoverPage {
	// items: Array<{industry: string, pitch: string, distance: string}>;
	items: any[];
	loading: boolean;
	errorMessage: string;

	username = '';
    email = '';

	constructor(public navCtrl: NavController, public navParams: NavParams, public menuCtrl: MenuController, private cardService: CardService) {
		this.loading = true;
		this.cardService.getDeck()
			.subscribe(
				cards => this.items = cards,
				error => this.errorMessage = <any>error,
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

	doFollow(e, item){
		e.stopPropagation();
		let data = {
		  user_id: 1,
		  card_id: item.id,
		  new_entry: !item.following
		};
		this.cardService.follow(data).subscribe(
			success => console.log(success),
			error => this.errorMessage = <any>error
		);
		// dangerous optimism!
		item.following = !item.following;
	}
}