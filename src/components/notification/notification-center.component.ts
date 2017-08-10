import { Component, Input, Output, EventEmitter } from '@angular/core';
import { Http } from '@angular/http'
import 'rxjs/add/operator/map';
import { WallProvider } from '../../providers/wall/wall';
import { Notification } from '../../providers/notification/notification';

@Component({
  selector: 'notification-center-component',
  templateUrl: 'notification-center-component.html'
})
export class NotificationCenterComponent {
  @Input() inheritedContent: any;
  @Output() goToCard: EventEmitter<any> = new EventEmitter();
  @Output() goToProfile: EventEmitter<any> = new EventEmitter();
  notifications: Notification[] = [];
  loading: boolean;

  constructor(public http: Http, private wall: WallProvider) {}

  ngOnInit() {
    this.loading = true;
    // this.notifications.push(new Notification('Title', 'and the message too.', null, null));
    // this.notifications.push(new Notification('Title', 'and the message too.', null, true));

    // this.wall.getWall(this.card_id)
    //   .subscribe(
    //     comments => {this.comments = comments; console.log(this.comments)},
    //     error => console.log(error),
    //     () => this.loading = false
    //   );
    this.loading = false;
  }

  notifTapped(item) {
    // markAsRead();
    this.goToCard.emit(item.card);
	}

  handleUserTapped(item) {
    this.goToProfile.emit(item);
  }

  doInfinite(e){
    console.log(e);
    setTimeout(function(){
      e.complete();
    }, 1000);
    // this.service.getCard()
    // .subscribe(
    //   data => this.comments.push(...data.results),
    //   err => console.log(err),
    //   () => e.complete()
    // );
  }

  doRefresh (e) {
  	setTimeout(function(){
  		e.complete();
  	}, 1000);
  	// this.service.getCard()
  	// .subscribe(
  	// 	data => this.item = data.results,
  	// 	err => console.log(err),
  	// 	() => e.complete()
  	// );
  }
}
