import { Component, Input, Output, EventEmitter } from '@angular/core';

@Component({
  selector: 'notification-component',
  templateUrl: 'notification-component.html'
})
export class NotificationComponent {
  @Input() item: any;
  @Output() handleUserTapped: EventEmitter<any> = new EventEmitter();
  founder: boolean = false;

  constructor() {}

  ngOnInit() {
    this.founder = this.item.user && this.item.card.founder_id == this.item.user.id;
  }

  userTapped(e, user_id) {
  	e.stopPropagation();
  	this.handleUserTapped.emit(user_id);
  }
}
