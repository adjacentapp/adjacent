'use strict';

let DEV = false;
// DEV = true;

let localURL = "http://localhost:9000/api/v3/";
// let remoteURL = "http://adjacent-env.btwkki4rra.us-west-2.elasticbeanstalk.com/api/v3/"
let remoteURL = "https://adjacentapp.com/api/v3/";

export const ENCRYPTION_KEY = "aether12292015";

export const BASE_API_URL = DEV ? localURL : remoteURL;

export let firstSignIn = false;
export let setFirstSignInTrue = () => {
  firstSignIn = true;
};

export const INDUSTRIES: string[] = [
	'Agriculture',
	'Art',
	'Architecture',
	'Business',
	'Computer Science',
	'Design',
	'Education',
	'Engineering',
	'Environment',
	'Fashion',
	'Finance',
	'Gaming',
	'Government',
	'Healthcare',
	'Humanities',
	'Journalism',
	'Language',
	'Law',
	'Lifestyle',
	'Marketing',
	'Math',
	'Music',
	'Performing Arts',
	'Science',
	'Social Impact',
	'Sports',
	'Transportation',
	'Urban Planning',
	'Writing'
];

export const STAGES: string[] = [
	'Couch Entrepreneur',
	'Taking First Step',
	'"Kickstarting" The Engine',
	'Running The Company',
	'Scaling The Business',
	'$$$'
];

export let SKILLS: any[] = [
	{ id: '3', name: 'Business' },
	{ id: '4', name: 'Marketing' },
	{ id: '5', name: 'Legal' },
	{ id: '6', name: 'Accounting' },
	{ id: '7', name: 'Data Science' },
	{ id: '8', name: 'Finance' },
  { id: '9', name: 'Fundraising' },
  { id: '10', name: 'Graphic Design' },
  { id: '11', name: 'Human Resources' },
  { id: '12', name: 'Photography' },
  { id: '13', name: 'Research' },
  { id: '14', name: 'Sales' },
  { id: '15', name: 'Back-end Development' },
  { id: '16', name: 'Front-end Development' },
  { id: '17', name: 'Industry Expertise' },
  { id: '18', name: 'Mechanical Engineering' },
  { id: '19', name: 'Operations' },
  { id: '20', name: 'Taxes' },
  { id: '21', name: 'UI/UX Design' },
	{ id: '22', name: 'Writing' }
];
export let setSkills = (skills) => {
	SKILLS = skills;
};

export let SHARE_URL: string = 'adjacentapp.com';
export let setShareURL = (url) => {
  SHARE_URL = url;
};

export let NETWORKS: any[] = [
	{ id: '0', name: 'Public' }
];
export let setNetworks = (networks) => {
  NETWORKS = networks;
};

export let ftueFilters = false;
export let setFtueFilters = () => {
  ftueFilters = true;
};
export let clearFtueFilters = () => {
  ftueFilters = false;
};

export let firstFollow = true;
export let setFirstFollowFalse = () => {
	firstFollow = false;
};

export let introQuote = null;
export let setIntroQuote = (quote) => {
  introQuote = quote;
};
