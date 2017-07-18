import { Component } from '@angular/core';
import { NavController, NavParams, MenuController } from 'ionic-angular';
import { ShowCardPage } from '../../pages/card/show';
import { CardProvider } from '../../providers/card/card';
import { AuthProvider } from '../../providers/auth/auth';

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

	constructor(public navCtrl: NavController, public navParams: NavParams, public menuCtrl: MenuController, private card: CardProvider, private auth: AuthProvider) {//}
	// ngOnInit() {
		
		this.loading = true;
		this.card.getDeck(this.auth.currentUser.id)
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