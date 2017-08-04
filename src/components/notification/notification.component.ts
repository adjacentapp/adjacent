import { Component, Input, Output, EventEmitter } from '@angular/core';

@Component({
  selector: 'notification-component',
  templateUrl: 'notification-component.html'
})
export class NotificationComponent {
  @Input() item: any ;
  // @Input() handleUserTapped: any;
  @Output() handleUserTapped: EventEmitter<any> = new EventEmitter();

  constructor() {}

  userTapped(e, user_id) {
  	e.stopPropagation();
  	this.handleUserTapped.emit(user_id);
  }
}
