import { Component, Input, Output, EventEmitter } from '@angular/core';
import { Http } from '@angular/http'
import 'rxjs/add/operator/map';
import { WallProvider } from '../../providers/wall/wall';
import { NotificationProvider, Notification } from '../../providers/notification/notification';
import { AuthProvider } from '../../providers/auth/auth';
import { Badge } from '@ionic-native/badge';

@Component({
  selector: 'notification-center-component',
  templateUrl: 'notification-center-component.html'
})
export class NotificationCenterComponent {
  @Input() inheritedContent: any;
  @Output() goToCard: EventEmitter<any> = new EventEmitter();
  @Output() goToProfile: EventEmitter<any> = new EventEmitter();
  // items: Notification[] = [];
  loading: boolean;
  reachedEnd: boolean = false;
  limit: number = 10;

  constructor(public http: Http, private wall: WallProvider, private auth: AuthProvider, private notif: NotificationProvider, private badge: Badge) {
    this.loading = true;
    this.init();
  }

  init(){
    // this.items.push(new Notification('Title', 'and the message too.', null, null));
    // this.items.push(new Notification('Title', 'and the message too.', null, true));
    if(this.auth.currentUser){
      this.notif.getNotifications(this.auth.currentUser.id, 0, this.limit)
        .subscribe(
          items => this.reachedEnd = this.notif.items.length < this.limit,
          error => console.log(<any>error),
          () => {
            this.loading = false;
            this.listenForNew();
          }
        );
    }
    else
      setTimeout(() => {
        this.init();
      }, 500);
  }

  listenForNew(){
    let user_id = this.auth.currentUser ? this.auth.currentUser.id : 0;
    this.notif.getNewNotifications(user_id)
      .subscribe(
        items => {
          // console.log('new notifications:', items);
        },
        error => console.log(<any>error),
        () => setTimeout(() => {
          this.listenForNew();
        }, 5000)
      );
  }

  notifTapped(item) {
    if(item.unread){
      this.notif.markAsRead(this.auth.currentUser.id, item.card.id).subscribe(
          items => console.log('deleted: ', items),
          error => console.log(<any>error)
      );
      item.unread = false;
      this.badge.decrease(1);
      this.notif.unreadCount--;
    }
    this.goToCard.emit(item.card);
	}

  handleUserTapped(item) {
    this.goToProfile.emit(item);
  }

  doRefresh (e) {
    this.notif.markAllAsRead(this.auth.currentUser.id)
      .subscribe(
        items => {
          this.notif.unreadCount = 0;
          // this.notif.items.map((item) => {
          //   return item.map(attr => )
          // })
          for(let item of this.notif.items)
            item.unread = false;
        },
        error => console.log(<any>error),
        () => e.complete()
      );
  }

  doInfinite (): Promise<any> {
    return new Promise((resolve) => {
      setTimeout(() => {
        let offset = this.notif.items.length;
        this.notif.getNotifications(this.auth.currentUser.id, offset, this.limit)
          .subscribe(
          items => {
            this.reachedEnd = this.notif.items.length < this.limit;
            resolve();
          },
          error => console.log(<any>error)
        );
      }, 500);
    });
  }
}