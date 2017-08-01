import { Component, Input, Output, EventEmitter } from '@angular/core';
import { Http } from '@angular/http'
import 'rxjs/add/operator/map';
import { WallProvider } from '../../providers/wall/wall';

@Component({
  selector: 'notification-center-component',
  templateUrl: 'notification-center-component.html'
})
export class NotificationCenterComponent {
  @Input() inheritedContent: any;
  @Output() goToCard: EventEmitter<any> = new EventEmitter();
  notifications: any[] = [];
  loading: boolean;

  constructor(public http: Http, private wall: WallProvider) {}

  ngOnInit() {
    this.loading = true;
    this.notifications.push({title: 'The first one', message: 'And the message here.'})
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
    this.goToCard.emit(item);
	}

  doInfinite(e){
    console.log(e);
    setTimeout(function(){
      // e.complete();
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
