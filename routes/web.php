<?php
use Illuminate\Http\Request;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', [
// 	'uses' => 'HomeController@index',
// 	'as' => 'home',
// 	'middleware' => 'auth'
// ]);

Route::group(['middleware' => ['role:admin']], function() {
   //admin only routes
	Route::get('/users', [
		'uses' => 'UsersController@index',
		'as' => 'users',
		'middleware' => 'auth'
	]);

	Route::post('/user/create', [
		'uses' => 'UsersController@create',
		'as' => 'user.create',
		'middleware' => 'auth'
	]);

	Route::post('/user/delete', [
		'uses' => 'UsersController@delete',
		'as' => 'user.delete',
		'middleware' => 'auth'
	]);


	Route::post('/create-role', [
		'uses' => 'UsersController@createRole',
		'as' => 'create-role',
		'middleware' => 'auth'
	]);

	//SETTINGS
	Route::get('/offline', [
		'uses' => 'SettingsController@offline',
		'as' => 'offline',
		'middleware' => 'auth'
	]);

	Route::get('/online', [
		'uses' => 'SettingsController@online',
		'as' => 'online',
		'middleware' => 'auth'
	]);

	Route::get('/bees', [
		'uses' => 'SettingsController@beeRequests',
		'as' => 'bee-requests',
		'middleware' => 'auth'
	]);

	Route::get('/slipcodes', [
		'uses' => 'BEEController@slipcodes',
		'as' => 'slipcodes',
		'middleware' => 'auth'
	]);

	//LENDERS
	Route::get('/lenders', [
		'uses' => 'LendersController@index',
		'as' => 'lenders',
		'middleware' => 'auth'
	]);
	

	Route::post('/update-lender', [
		'uses' => 'LendersController@update',
		'as' => 'update-lender',
		'middleware' => 'auth'
	]);


	//data mining routes
	Route::prefix('data')->group(function (){
	    Route::get('/loans', [
	    	'uses' => 'DataController@loans'
	    ]);

	    //Apps CSV
	    Route::get('/apps', [
	    	'uses' => 'DataController@apps'
	    ]);

	    //Contacts CSV
	    Route::get('/contacts', [
	    	'uses' => 'DataController@contacts'
	    ]);


	    Route::get('/threatmark', [
	    	'uses' => 'DataController@threatmark'
	    ]);


	});


});

//SINGLE LENDER
Route::get('/lenders/{id}', [
	'uses' => 'LendersController@single',
	'as' => 'single-lender',
	'middleware' => 'auth'
]);


















Route::get('/', function(){
	return redirect()->route('home');
});

Route::get('/logout', function () {
    //return view('welcome');
    Auth::logout();
    return redirect()->route('login');
})->name('logout');

Auth::routes();

Route::get('/home', [
	'uses' => 'HomeController@index',
	'as' => 'home',
	'middleware' => 'auth'
]);

Route::get('/home/critical', [
	'uses' => 'HomeController@critical',
	'middleware' => 'auth'
]);

//block customer account
Route::post('/customer/block-customer-account', [
	'uses' => 'CustomersController@blockAccount',
	'as' => 'customer-block-account',
	'middleware' => 'auth'
]);

//unblock user account
Route::get('/customer/{id}/unblock', [
	'uses' => 'CustomersController@unblockAccount',
	//'as' => 'customer-unblock-account',
	'middleware' => 'auth'
]);
//Customers
Route::get('/customers', [
	'uses' => 'CustomersController@index',
	'as' => 'customers',
	'middleware' => 'auth'
]);


Route::get('/customers/referred', [
	'uses' => 'CustomersController@referred',
	'as' => 'referred',
	'middleware' => 'auth'
]);

Route::get('/customers/csv', [
	'uses' => 'CustomersController@customersCSV',
	'as' => 'customers-csv',
	'middleware' => 'auth'
]);

Route::get('/customers/test', [
	'uses' => 'CustomersController@test',
	'middleware' => 'auth'
]);

Route::get('/customers/idm', [
	'uses' => 'CustomersController@updateRanking',
	'middleware' => 'auth'
]);


Route::get('/customers/declined', [
	'uses' => 'CustomersController@declined',
	'as' => 'customers-declined',
	'middleware' => 'auth'
]);



Route::get('/daily-customers-statistics', [
	'uses' => 'CustomersController@dailyStatistics',
	'as' => 'daily-customers-statistics',
	'middleware' => 'auth'
]);

Route::get('/customers/search', array(
	'uses' => 'CustomersController@search',
	'as' => 'customers.search',
	'middleware' => 'auth'
));

Route::get('/customer/{id}', [
	'uses' => 'CustomersController@single',
	'as' => 'customer',
	'middleware' => 'auth'
]);

Route::get('/customer/{id}/penalties', [
	'uses' => 'CustomersController@penalties',
	'as' => 'customer.penalties',
	'middleware' => 'auth'
]);

Route::get('/customer/{id}/smss', [
	'uses' => 'CustomersController@customerSMSs',
	'as' => 'customer-smss',
	'middleware' => 'auth'
]);

Route::get('/customer/{id}/devices', [
	'uses' => 'CustomersController@customerDevices',
	'as' => 'customer-devices',
	'middleware' => 'auth'
]);

Route::get('/customer/{id}/pbf', [
	'uses' => 'CustomersController@phonebookFrequency',
	//'as' => 'customer-smss',
	'middleware' => 'auth'
]);

Route::get('/customer/{id}/smss/csv', [
	'uses' => 'CustomersController@customerSMSDownload',
	'as' => 'customer-smss-csv',
	'middleware' => 'auth'
]);

Route::get('/calls/{id}', [
	'uses' => 'CustomersController@customerCalls',
	'as' => 'customer-calls',
	'middleware' => 'auth'
]);

Route::get('/contacts/{id}', [
	'uses' => 'CustomersController@customerContacts',
	'as' => 'customer-contacts',
	'middleware' => 'auth'
]);

//LOANS
Route::get('/loans', [
	'uses' => 'LoansController@index',
	'as' => 'loans',
	'middleware' => 'auth'
]);

//Late loans
Route::get('/loans/late', [
	'uses' => 'LoansController@lateLoans',
	'as' => 'late-loans',
	'middleware' => 'auth'
]);

Route::get('/loans/late/paid', [
	'uses' => 'LoansController@latePaidLoans',
	'as' => 'late-paid-loans',
	'middleware' => 'auth'
]);

//All loans CSV
Route::get('/loans/csv', [
	'uses' => 'LoansController@loansCSV',
	'as' => 'loans-csv',
	'middleware' => 'auth'
]);

//Collection CSV
Route::get('/collection-csv', [
	'uses' => 'LoansController@questCSV',
	'as' => 'collection-csv',
	'middleware' => 'auth'
]);

Route::get('/loans-by-date', [
	'uses' => 'LoansController@daily',
	'as' => 'loans-by-date',
	'middleware' => 'auth'
]);

Route::get('/loans-by-date/csv', [
	'uses' => 'LoansController@dateRangeLoansCSV',
	'as' => 'loans-by-date-csv',
	'middleware' => 'auth'
]);

//Unclaimed repayments
Route::get('/unclaimed', [
	'uses' => 'UnclaimedTransactionsController@index',
	'as' => 'unclaimed',
	'middleware' => 'auth'
]);

Route::get('/unclaimed-search', [
	'uses' => 'UnclaimedTransactionsController@search',
	'as' => 'unclaimed-search',
	'middleware' => 'auth'
]);

Route::get('/unclaimed/csv', [
	'uses' => 'UnclaimedTransactionsController@unclaimedCSV',
	'as' => 'unclaimed.csv',
	'middleware' => 'auth'
]);

//Repayments
Route::get('/repayments', [
	'uses' => 'RepaymentsController@index',
	'as' => 'repayments',
	'middleware' => 'auth'
]);

Route::get('/repayments-search', [
	'uses' => 'RepaymentsController@search',
	'as' => 'repayments-search',
	'middleware' => 'auth'
]);

Route::get('/daily-repayments', [
	'uses' => 'RepaymentsController@dailyRepayments',
	'as' => 'daily-repayments',
	'middleware' => 'auth'
]);

Route::get('/repayments/csv', [
	'uses' => 'RepaymentsController@repaymentsCSV',
	'as' => 'repayments-csv',
	'middleware' => 'auth'
]);


//Financial reports
Route::get('/reports', [
	'uses' => 'ReportsController@index',
	'as' => 'reports',
	'middleware' => 'auth'
]);


Route::get('/reports/date-range', [
	'uses' => 'ReportsController@dateRangeData',
	'as' => 'date-range-data',
	'middleware' => 'auth'
]);

Route::get('/reports/monthly-data', [
	'uses' => 'ReportsController@monthlyData',
	'as' => 'monthly-data',
	'middleware' => 'auth'
]);

Route::get('/reports/seven-days-financials', [
	'uses' => 'ReportsController@sevenDaysFinancials',
	'as' => 'seven-days-financials',
	'middleware' => 'auth'
]);



Route::get('/financials', [
	'uses' => 'FinancialsController@index',
	'as' => 'financials',
	'middleware' => 'auth'
]);

//income
Route::get('/financials/income', [
	'uses' => 'FinancialsController@income',
	'as' => 'financials.income',
	'middleware' => 'auth'
]);
//expenses
Route::get('/financials/expenses', [
	'uses' => 'FinancialsController@expenses',
	'as' => 'financials.expenses',
	'middleware' => 'auth'
]);

//get customer CI loan history

Route::get('/ci-data', [
	'uses' => 'ReportsController@monthlyData',
	'as' => 'ci-data',
	'middleware' => 'auth'
]);

Route::get('/debt-collection', [
	'uses' => 'ReportsController@collectorCSV',
	'as' => 'debt-collection',
	'middleware' => 'auth'
]);


//Expenses
Route::get('/expenses/csv', [
	'uses' => 'ExpensesController@expensesCSV',
	'middleware' => 'auth'
]);

Route::get('/expenses', [
	'uses' => 'ExpensesController@index',
	'as' => 'expenses',
	'middleware' => 'auth'
]);

Route::get('/expenses/csv', [
	'uses' => 'ExpensesController@expensesCSV',
	'middleware' => 'auth'
]);

Route::get('/disbursements/csv', [
	'uses' => 'ExpensesController@disbursementsCSV',
	'middleware' => 'auth'
]);

Route::get('/penalties/csv', [
	'uses' => 'PenaltiesController@penaltiesCSV',
	'middleware' => 'auth'
]);

Route::get('/expenses-data', [
	'uses' => 'ExpensesController@dateRangeData',
	'as' => 'expenses-data',
	'middleware' => 'auth'
]);

//CRB Listing
Route::get('/crb', [
	'uses' => 'CRBController@listCRB',
	'as' => 'crb',
	'middleware' => 'auth'
]);

Route::get('/bee-loans', [
	'uses' => 'BEEController@loans',
	'as' => 'bee-loans',
	'middleware' => 'auth'
]);


Route::get('/bee-files', [
	'uses' => 'BEEController@beeRequests',
	'as' => 'bee-files',
	'middleware' => 'auth'
]);



//Debt Collection
Route::get('/debt-collection', [
	'uses' => 'DebtsController@index',
	'as' => 'debt-collection',
	'middleware' => 'auth'
]);


Route::get('/debt-collection-list', [
	'uses' => 'DebtsController@collectorJSON',
	'as' => 'debt-collection-list',
	'middleware' => 'auth'
]);

Route::get('/debt-collection-csv', [
	'uses' => 'DebtsController@collectorCSV',
	'as' => 'debt-collection-csv',
	'middleware' => 'auth'
]);


//Analysis
Route::get('/data-analysis', [
	'uses' => 'AnalysisController@loansData',
	'as' => 'data-analysis',
	'middleware' => 'auth'
]);

Route::get('/monthly-data-analysis/{month}', [
	'uses' => 'AnalysisController@loansMonthlyData',
	'as' => 'monthly-data-analysis',
	'middleware' => 'auth'
]);


//Analysis
Route::get('/portfolio', [
	'uses' => 'ReportsController@portfolioMonitoring',
	'as' => 'portfolio',
	'middleware' => 'auth'
]);


Route::get('/performance/monthly', [
	'uses' => 'PerformanceController@monthly',
	'as' => 'performance.monthly',
	'middleware' => 'auth'
]);



//LENDERS
//LENDER HOME
Route::get('/lending/home', [
	'uses' => 'LenderPortfolioController@home',
	'as' => 'lenders-home',
	'middleware' => 'auth'
]);



//Internal debt collection

Route::get('/internal-debt-collection', [
	'uses' => 'InternalDebtCollectionController@index',
	'as' => 'internal-debt-collection',
	'middleware' => 'auth'
]);

Route::get('/internal-debt-collection-json', [
	'uses' => 'InternalDebtCollectionController@collectorJSON',
	'as' => 'internal-debt-collection-json',
	'middleware' => 'auth'
]);


Route::post('/commitment', [
	'uses' => 'InternalDebtCollectionController@createCommitment',
	'as' => 'create-commitment',
	'middleware' => 'auth'
]);

Route::get('/commitments', [
	'uses' => 'InternalDebtCollectionController@commitmentsView',
	'as' => 'commitments',
	'middleware' => 'auth'
]);

Route::get('/commitments-json', [
	'uses' => 'InternalDebtCollectionController@commitmentJSON',
	'as' => 'commitments-json',
	'middleware' => 'auth'
]);

Route::get('/collection-calls', [
	'uses' => 'InternalDebtCollectionController@collectionCallsView',
	'as' => 'collection-calls',
	'middleware' => 'auth'
]);

Route::get('/collection-calls-json', [
	'uses' => 'InternalDebtCollectionController@collectionCallsJSON',
	'as' => 'collection-calls-json',
	'middleware' => 'auth'
]);




// WALLET ROUTES
Route::get('/wallet', [
	'uses' => 'WalletsController@index',
	'as' => 'wallet',
	'middleware' => 'auth'
]);
Route::get('/customer/{id}/wallet', [
	'uses' => 'WalletsController@customerWallet',
	'as' => 'customer.wallet',
	'middleware' => 'auth'
]);



