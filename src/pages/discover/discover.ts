import { Component, ViewChild, ElementRef } from '@angular/core';
import { NavController, NavParams, MenuController, Slides } from 'ionic-angular';
import { ShowCardPage } from '../../pages/card/show';
import { JoinNetworkPage } from '../../pages/network/join';
import { CardProvider, Card } from '../../providers/card/card';
import { AuthProvider } from '../../providers/auth/auth';
import { MessagesProvider } from '../../providers/messages/messages';
import { NotificationProvider } from '../../providers/notification/notification';
import { PopoverController } from 'ionic-angular';
import { FilterPage } from '../../pages/filter/filter';
import { FtueFilterPage } from '../../pages/filter/ftue-filter';
import { FtueHamburgerPage } from '../../pages/filter/ftue-hamburger';
import * as globs from '../../app/globals'

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
  filters: any = {
    industry: "Any",
    network_ids: localStorage.network_ids ? localStorage.network_ids.split(',') : [0]
  };
  introQuote = null;

	errorMessage: string;

	username = '';
    email = '';

	constructor(public navCtrl: NavController, public navParams: NavParams, public menuCtrl: MenuController, private card: CardProvider, private auth: AuthProvider, private msg: MessagesProvider, private notif: NotificationProvider, public popoverCtrl: PopoverController, private eleRef: ElementRef) {
		this.getDeck();
	}

  ngAfterViewInit (){
    if(globs.ftueFilters){
      setTimeout(() => {
        this.showFtueFilter();
        globs.clearFtueFilters();
       }, 400);
     }
  }

	getDeck() {
    this.loading = true;
		this.card.getDeck(this.auth.currentUser.id, 0, this.filters)
			.subscribe(
				success => {
          this.items = success;
          this.introQuote = globs.introQuote;
        },
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
		if(!this.reachedEnd && !this.dealing && currentIndex >= this.items.length - 3) // deal more cards when 3 away from end
			this.dealCards();
	}

	dealCards() {
		this.dealing = true;
		let offset = this.items.length;
		this.card.getDeck(this.auth.currentUser.id, offset, this.filters)
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
    this.dealing = true;
	  this.card.getDeck(this.auth.currentUser.id, 0, this.filters)
	  	.subscribe(
	  		items => {
	  			this.items = items;
	  			this.slides.slideTo(1, 400);
          this.reachedEnd = false;
	  		},
	  		error => this.errorMessage = <any>error,
	  		() => {
          e.complete();
          this.dealing = false;
        }
	  	);
	}

  joinNetwork(){
    this.navCtrl.push(JoinNetworkPage);
  }

  showFilter(e) {
    let popover = this.popoverCtrl.create(FilterPage, this.filters);
    popover.present({
      ev: e
    });
    popover.onDidDismiss(data => {
      if(data && data.changed){
        this.filters = data;
        this.getDeck();
      }
      else if(data && data.go_to_join){
        this.navCtrl.push(JoinNetworkPage);
      }
    });
  }

  showFtueFilter() {
    let mockEvent = {
      target: {
        getBoundingClientRect : () => {
          return document.getElementById('filter_button').getBoundingClientRect()
        }
      }
    }
    let popover = this.popoverCtrl.create(FtueFilterPage);
    popover.present({
      ev: mockEvent
    });
    popover.onDidDismiss(data => {
      // this.showFilter(e)
      this.showFtueHamburger()
    });
  }

  showFtueHamburger() {
    let mockEvent = {
      target: {
        getBoundingClientRect : () => {
          return document.getElementById('hamburger_button').getBoundingClientRect()
        }
      }
    }
    let popover = this.popoverCtrl.create(FtueHamburgerPage);
    popover.present({
      ev: mockEvent
    });
    popover.onDidDismiss(data => {
      // this.showFilter(e)
    });
  }

}
