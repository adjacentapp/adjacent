import { Component, ViewChild } from '@angular/core';
import { NavController, NavParams, MenuController, Slides } from 'ionic-angular';
import { ShowCardPage } from '../../pages/card/show';
import { JoinNetworkPage } from '../../pages/network/join';
import { CardProvider, Card } from '../../providers/card/card';
import { AuthProvider } from '../../providers/auth/auth';
import { MessagesProvider } from '../../providers/messages/messages';
import { NotificationProvider } from '../../providers/notification/notification';
import { PopoverController } from 'ionic-angular';
import { FilterPage } from '../../pages/filter/filter';
import * as globs from '../../app/globals'

@Component({
	selector: 'ftue-page',
	templateUrl: 'ftue.html'
})
export class FtuePage {
	@ViewChild(Slides) slides: Slides;
	items: Card[];
	loading: boolean;
	reachedEnd: boolean = false;
	dealing: boolean = false;
  
  track: string;
  bounce: boolean = false;

	constructor(public navCtrl: NavController, public navParams: NavParams, public menuCtrl: MenuController, private card: CardProvider, private auth: AuthProvider, private msg: MessagesProvider, private notif: NotificationProvider, public popoverCtrl: PopoverController) {
		// this.getDeck();
	}

	getDeck() {
  //   this.loading = true;
		// this.card.getDeck(this.auth.currentUser.id, 0, this.filters)
		// 	.subscribe(
		// 		success => {
  //         this.items = success;
  //         this.introQuote = globs.introQuote;
  //       },
		// 		error => {
		// 			console.log('trying to get_cards again');
		// 			this.getDeck();
		// 			this.errorMessage = <any>error;
		// 		},
		// 		() => this.loading = false
		// 	);
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

  setTrack(track) {
    this.track = track;
    setTimeout(() => {
      this.slides.slideTo(3, 400);
    }, 300);
  }

  triggerBounce(){
    console.log('bounce')
    this.bounce = true;
    setTimeout(() => {
      this.bounce = false;
    }, 600)
  }

	slideChanged() {
		let currentIndex = this.slides.getActiveIndex();
		// if(!this.reachedEnd && !this.dealing && currentIndex >= this.items.length - 3){ // deal more cards when 3 away from end
			// this.dealCards();
    // }
	}


}
