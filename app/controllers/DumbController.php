<?php

namespace simplerest\controllers;

use simplerest\core\Controller;
use simplerest\core\Request;
use simplerest\libs\Factory;
use simplerest\libs\Debug;
use simplerest\libs\DB;
use simplerest\models\UsersModel;
use simplerest\models\ProductsModel;
use simplerest\models\UserRolesModel;
use PHPMailer\PHPMailer\PHPMailer;
use simplerest\libs\Utils;
use simplerest\libs\Validator;
use GuzzleHttp\Client;
//use Guzzle\Http\Message\Request;
//Guzzle\Http\Message\Response

class DumbController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function index(){
        return 'INDEX';
    }

    function add($a, $b){
        $res = (int) $a + (int) $b;
        return  "$a + $b = " . $res;
    }

    function mul(){
        $req = Request::getInstance();
        $res = (int) $req[0] * (int) $req[1];
        return "$req[0] * $req[1] = " . $res;
    }

    function div(){
        $res = (int) @Request::getParam(0) / (int) @Request::getParam(1);
        //
        // hacer un return en vez de un "echo" me habilita a manipular
        // la "respuesta", conviertiendola a JSON por ejemplo 
        //
        return ['result' => $res];
    }

    function login(){
		//$this->view('login.php');
	}
	

    /*
    function mul(Request $req){
        $res = (int) $req[0] * (int) $req[1];
        echo "$req[0] + $req[1] = " . $res;
    }
    */

    function get_products(){
        Debug::dd(DB::table('products')->get());
        //Debug::dd(DB::table('products')->setFetchMode('ASSOC')->get());
    }

    function get_products2(){
        Debug::dd(DB::table('products')->where(['size', '2L'])->get());
    }


    function create_p(){

        $name = '';
        for ($i=0;$i<20;$i++)
            $name .= chr(rand(97,122));

        $id = DB::table('products')->create([ 
            'name' => $name, 
            'description' => 'Esto es una prueba', 
            'size' => '1L',
            'cost' => 66,
            'belongs_to' => 90
        ]);    
    }

    function transaction(){
        DB::beginTransaction();

        try {
            $name = '';
            for ($i=0;$i<20;$i++)
                $name .= chr(rand(97,122));

            $id = DB::table('products')->create([ 
                'name' => $name, 
                'description' => 'Esto es una prueba!!!', 
                'size' => rand(1,5).'L',
                'cost' => rand(0,500),
                'belongs_to' => 90
            ]);   

            throw new \Exception("AAA"); 

            DB::commit();

        } catch (\Exception $e) {
            echo 'ACA';
            DB::rollback();
            throw $e;
        } catch (\Throwable $e) {
            echo 'ACA 2';
            DB::rollback();            
        }            
    }

    // https://fideloper.com/laravel-database-transactions
    function transaction2(){
        DB::transaction(function(){
            $name = '';
            for ($i=0;$i<20;$i++)
                $name .= chr(rand(97,122));

            $id = DB::table('products')->create([ 
                'name' => $name, 
                'description' => 'Esto es una prueba', 
                'size' => rand(1,5).'L',
                'cost' => rand(0,500),
                'belongs_to' => 90
            ]);   

            throw new Exception("AAA"); 
        });      
    }
    
    function output_mutator(){
        $rows = DB::table('users')
        ->registerOutputMutator('username', function($str){ return strtoupper($str); })
        ->get();

        Debug::dd($rows);
    }

    function output_mutator2(){
        $rows = DB::table('products')
        ->registerOutputMutator('size', function($str){ return strtolower($str); })
        ->groupBy(['size'])
        ->having(['AVG(cost)', 150, '>='])
        ->select(['size'])
        ->selectRaw('AVG(cost)')
        ->get();

        Debug::dd($rows);
    }

    /*
        El problema de los campos ocultos es que rompen los transformers
        usar when() en su lugar

        https://laravel.com/docs/5.5/eloquent-resources
    */
    function transform(){
        //$this->is_admin = true;


        $t = new \simplerest\transformers\UsersTransformer();

        $rows = DB::table('users')
        ->registerTransformer($t, $this)
        ->get();

        Debug::dd($rows);
    }

    function transform_and_output_mutator(){
        $t = new \simplerest\transformers\UsersTransformer();

        $rows = DB::table('users')
        ->registerOutputMutator('username', function($str){ return strtoupper($str); })
        ->registerTransformer($t)
        ->get();

        Debug::dd($rows);
    }

    function transform2(){
        $t = new \simplerest\transformers\ProductsTransformer();

        $rows = DB::table('products')
        ->where(['size'=>'2L'])
        ->registerTransformer($t)
        ->get();

        Debug::dd($rows);
    }



    function limit(){
        Debug::dd(DB::table('products')->offset(20)->limit(10)->get());
        Debug::dd(DB::getQueryLog());

        Debug::dd(DB::table('products')->limit(10)->get());
        Debug::dd(DB::getQueryLog());
    }
    
    ///
    function limite(){
        DB::table('products')->offset(20)->limit(10)->get();
        Debug::dd(DB::getQueryLog());

        DB::table('products')->limit(10)->get();
        Debug::dd(DB::getQueryLog());
    }

    function sub1(){
        // SELECT COUNT(*) FROM (SELECT  name, size FROM products  GROUP BY size ) as sub 
        $sub = DB::table('products')
        ->select(['name', 'size'])
        ->groupBy(['size']);
    
        $conn = DB::getConnection();
    
        $m = new \simplerest\core\Model($conn);
        $res = $m->fromRaw("({$sub->toSql()}) as sub")->count();
    
        //Debug::dd($sub->toSql());
        Debug::dd($m->getLastPrecompiledQuery());
        //Debug::dd(DB::getQueryLog());     
    }

    function sub1a(){
        $sub = DB::table('products')
        ->select(['id', 'name', 'size'])
        ->where(['cost', 150, '>=']);
    
        $conn = DB::getConnection();
    
        $m = new \simplerest\core\Model($conn);
        $res = $m->fromRaw("({$sub->toSql()}) as sub")->count();
    
        //Debug::dd($sub->toSql());
        //Debug::dd($m->getLastPrecompiledQuery());
        //Debug::dd(DB::getQueryLog());     
    }

    function distinct(){
        Debug::dd(DB::table('products')->distinct()->get(['size']));

        // Or
        Debug::dd(DB::table('products')->distinct(['size'])->get());

        // Or
        Debug::dd(DB::table('products')->select(['size'])->distinct()->get());
    }

    function distinct1(){
        Debug::dd(DB::table('products')->select(['size', 'cost'])->distinct()->get());
    }

    function distinct2(){
        Debug::dd(DB::table('users')->distinct()->get());
    }

    function distinct3(){
        Debug::dd(DB::table('products')->distinct()->get());
    }

    function pluck(){
        $names = DB::table('products')->pluck('size');

        foreach ($names as $name) {
            echo "$name <br/>";
        }
    }

    function get_product($id){       
        // Include deleted items
        Debug::dd(DB::table('products')->where(['id' => $id])->showDeleted()->get());
    }
    
    function exists(){       
        Debug::dd(DB::table('users')->where(['username' => 'boctulus'])->exists());                
    }
           
    function first(){
        Debug::dd(DB::table('products')->where([ 
            ['cost', 50, '>='],
            ['cost', 500, '<='],
            ['belongs_to',  90]
        ])->first(['name', 'size', 'cost'])); 
    }

    function value(){
        Debug::dd(DB::table('products')->where([ 
            ['cost', 200, '>='],
            ['cost', 500, '<='],
            ['belongs_to',  90]
        ])->value('name')); 
    }

    function oldest(){
        // oldest first
        Debug::dd(DB::table('products')->oldest()->get());
    }

    function newest(){
        // newest, first result
        Debug::dd(DB::table('products')->newest()->first());
    }
    
    // random or rand
    function random(){
        Debug::dd(DB::table('products')->random()->limit(5)->get(['id', 'name']));

        Debug::dd(DB::table('products')->random()->select(['id', 'name'])->first());
    }

    function count(){
        $c = DB::table('products')
        ->where([ 'belongs_to'=> 90] )
        ->count();

        Debug::dd($c);
    }

    function count1(){
        $c = DB::table('products')
        ->where([ 'belongs_to'=> 90] )
        ->count('*', 'count');

        var_dump($c);
        Debug::dd(DB::getQueryLog());
    }

    function count1b(){
        // SELECT COUNT(*) FROM products WHERE cost >= 100 AND size = '1L' AND belongs_to = 90 AND deleted_at IS NULL 

        $res =  DB::table('products')
        ->where([ ['cost', 100, '>='], ['size', '1L'], ['belongs_to', 90] ])
        ->count();

        Debug::dd($res);
        Debug::dd(DB::getQueryLog());
    } 

    function count2(){
        // SELECT COUNT(DISTINCT description) FROM products WHERE cost >= 100 AND size = '1L' AND belongs_to = 90 AND deleted_at IS NULL  

        $res = DB::table('products')
        ->where([ ['cost', 100, '>='], ['size', '1L'], ['belongs_to', 90] ])
        ->distinct()
        ->count('description');

        Debug::dd($res);
        Debug::dd(DB::getQueryLog());
    }

    function count2b(){
        // SELECT COUNT(DISTINCT description) FROM products WHERE cost >= 100 AND size = '1L' AND belongs_to = 90 AND deleted_at IS NULL  

        $res = DB::table('products')
        ->where([ ['cost', 100, '>='], ['size', '1L'], ['belongs_to', 90] ])
        ->distinct()
        ->count('description', 'count');

        Debug::dd($res);
        Debug::dd(DB::getQueryLog());
    }

    function avg(){
        // SELECT AVG(cost) FROM products WHERE cost >= 100 AND size = '1L' AND belongs_to = 90 AND deleted_at IS NULL; 

        $res = DB::table('products')
        ->where([ ['cost', 100, '>='], ['size', '1L'], ['belongs_to', 90] ])
        ->avg('cost', 'prom');

        var_dump($res);
    }

    function sum(){
        // SELECT SUM(cost) FROM products WHERE cost >= 100 AND size = '1L' AND belongs_to = 90 AND deleted_at IS NULL; 

        $res = DB::table('products')
        ->where([ ['cost', 100, '>='], ['size', '1L'], ['belongs_to', 90] ])
        ->sum('cost', 'suma');

        var_dump($res);
    }

    function min(){
        // SELECT MIN(cost) FROM products WHERE cost >= 100 AND size = '1L' AND belongs_to = 90 AND deleted_at IS NULL; 

        $res = DB::table('products')
        ->where([ ['cost', 100, '>='], ['size', '1L'], ['belongs_to', 90] ])
        ->min('cost', 'minimo');

        var_dump($res);
    }

    function max(){
        // SELECT MIN(cost) FROM products WHERE cost >= 100 AND size = '1L' AND belongs_to = 90 AND deleted_at IS NULL; 

        $res =  DB::table('products')
        ->where([ ['cost', 100, '>='], ['size', '1L'], ['belongs_to', 90] ])
        ->max('cost', 'maximo');

        var_dump($res);
    }

    /*
        select and addSelect
    */
    function select() {
        Debug::dd(DB::table('products')->random()->select(['id', 'name'])->addSelect('cost')->first());
    }

    /*
        RAW select

        pluck() no se puede usar con selectRaw() si posee un "as" pero la forma de lograr lo mismo
        es seteando el "fetch mode" en "COLUMN"

        Investigar como funciona el pluck() de Larvel
        https://stackoverflow.com/a/40964361/980631
    */
    function select2() {
        Debug::dd(DB::table('products')->setFetchMode('COLUMN')
        ->selectRaw('cost * ? as cost_after_inc', [1.05])->get());
    }

    function select3() {
        Debug::dd(DB::table('products')->setFetchMode('COLUMN')
        ->where([ ['cost', 100, '>='], ['size', '1L'], ['belongs_to', 90] ])
        ->selectRaw('cost * ? as cost_after_inc', [1.05])->get());
    }

    function select3a() {
        Debug::dd(DB::table('products')
        ->where([ ['cost', 100, '>='], ['size', '1L'], ['belongs_to', 90] ])
        ->selectRaw('cost * ? as cost_after_inc', [1.05])->distinct()->get());
    }

    function select3b() {
        Debug::dd(DB::table('products')->setFetchMode('COLUMN')
        ->where([ ['cost', 100, '>='], ['size', '1L'], ['belongs_to', 90] ])
        ->selectRaw('cost * ? as cost_after_inc', [1.05])->distinct()->get());
    }

    function select4() {
        Debug::dd(DB::table('products')
        ->where([ ['cost', 100, '>='], ['size', '1L'], ['belongs_to', 90] ])
        ->selectRaw('cost * ? as cost_after_inc', [1.05])
        ->addSelect('name', 'cost')
        ->get());
    }

    /*
        La ventaja de usar select() - por sobre usar get() - es que se ejecuta antes que count() permitiendo combinar selección de campos con COUNT() 

        SELECT size, COUNT(*) FROM products GROUP BY size
    */
    function select_group_count(){
        Debug::dd(DB::table('products')->showDeleted()
        ->groupBy(['size'])->select(['size'])->count());
    }

    /*
        SELECT size, AVG(cost) FROM products GROUP BY size
    */
    function select_group_avg(){
        Debug::dd(DB::table('products')->showDeleted()
        ->groupBy(['size'])->select(['size'])
        ->avg('cost'));    
    }

    function filter_products1(){
        Debug::dd(DB::table('products')->showDeleted()->where([ 
            ['size', '2L']
        ])->get());
    }
    
    function filter_products2(){
        Debug::dd(DB::table('products')
        ->where([ 
            ['name', ['Vodka', 'Wisky', 'Tekila','CocaCola']], // IN 
            ['locked', 0],
            ['belongs_to', 90]
        ])
        ->whereNotNull('description')
        ->get());
    }

    // SELECT * FROM products WHERE name IN ('CocaCola', 'PesiLoca') OR cost IN (100, 200)  OR cost >= 550 AND deleted_at IS NULL
    function filter_products3(){

        Debug::dd(DB::table('products')->where([ 
            ['name', ['CocaCola', 'PesiLoca']], 
            ['cost', 550, '>='],
            ['cost', [100, 200]]
        ], 'OR')->get());    
    }

    function filter_products4(){    
        Debug::dd(DB::table('products')->where([ 
            ['name', ['CocaCola', 'PesiLoca', 'Wisky', 'Vodka'], 'NOT IN']
        ])->get());
    }

    function filter_products5(){
        // implicit 'AND'
        Debug::dd(DB::table('products')->where([ 
            ['cost', 200, '<'],
            ['name', 'CocaCola'] 
        ])->get());        
    }

    function filter_products6(){
        Debug::dd(DB::table('products')->where([ 
            ['cost', 200, '>='],
            ['cost', 270, '<=']
        ])->get());            
    }

    // WHERE IN
    function where1(){
        Debug::dd(DB::table('products')->where(['size', ['0.5L', '3L'], 'IN'])->get());
    }

    // WHERE IN
    function where2(){
        Debug::dd(DB::table('products')->where(['size', ['0.5L', '3L']])->get());
    }

    // WHERE IN
    function where3(){
        Debug::dd(DB::table('products')->whereIn('size', ['0.5L', '3L'])->get());
    }

    //WHERE NOT IN
    function where4(){
        Debug::dd(DB::table('products')->where(['size', ['0.5L', '3L'], 'NOT IN'])->get());
    }

    //WHERE NOT IN
    function where5(){
        Debug::dd(DB::table('products')->whereNotIn('size', ['0.5L', '3L'])->get());
    }

    // WHERE NULL
    function where6(){  
        Debug::dd(DB::table('products')->where(['workspace', null])->get());   
    }

    // WHERE NULL
    function where7(){  
        Debug::dd(DB::table('products')->whereNull('workspace')->get());
    }

    // WHERE NOT NULL
    function where8(){  
        Debug::dd(DB::table('products')->where(['workspace', null, 'IS NOT'])->get());   
    }

    // WHERE NOT NULL
    function where9(){  
        Debug::dd(DB::table('products')->whereNotNull('workspace')->get());
    }

    // WHERE BETWEEN
    function where10(){
        Debug::dd(DB::table('products')
        ->select(['name', 'cost'])
        ->whereBetween('cost', [100, 250])->get());
    }

    // WHERE BETWEEN
    function where11(){
        Debug::dd(DB::table('products')
        ->select(['name', 'cost'])
        ->whereNotBetween('cost', [100, 250])->get());
    }
    
    function where12(){
        Debug::dd(DB::table('products')
        ->find(103));
    }

    function where13(){
        Debug::dd(DB::table('products')
        ->where(['cost', 150])
        ->value('name'));
    }

    /*
        SELECT  name, cost, id FROM products WHERE belongs_to = '90' AND (cost >= 100 AND cost < 500) AND description IS NOT NULL
    */
    function where14(){
        Debug::dd(DB::table('products')->showDeleted()
        ->select(['name', 'cost', 'id'])
        ->where(['belongs_to', 90])
        ->where([ 
            ['cost', 100, '>='],
            ['cost', 500, '<']
        ])
        ->whereNotNull('description')
        ->get());
    }

    // SELECT  name, cost, id FROM products WHERE belongs_to = '90' AND (name IN ('CocaCola', 'PesiLoca')  OR cost >= 550 OR cost < 100) AND description IS NOT NULL
    function where_or(){
        Debug::dd(DB::table('products')->showDeleted()
        ->select(['name', 'cost', 'id'])
        ->where(['belongs_to', 90])
        ->where([ 
            ['name', ['CocaCola', 'PesiLoca']], 
            ['cost', 550, '>='],
            ['cost', 100, '<']
        ], 'OR')
        ->whereNotNull('description')
        ->get());
    }
    
    /* 
        A OR (B AND C)

        SELECT  name, cost, id FROM products WHERE (1 = 1)  AND belongs_to = ?  OR name IN ('CocaCola', 'PesiLoca') OR (cost <= ? AND cost >= ?)
    */
    function or_where(){
        Debug::dd(DB::table('products')->showDeleted()
        ->select(['name', 'cost', 'id'])
        ->where(['belongs_to', 90])
        ->orWhere(['name', ['CocaCola', 'PesiLoca']])
        ->orWhere([
            ['cost', 550, '<='],
            ['cost', 100, '>=']
        ])
        ->get());
    }
    
    // A OR (B AND C)
    function or_where2(){
        Debug::dd(DB::table('products')->showDeleted()
        ->select(['name', 'cost', 'id', 'description'])
        ->whereNotNull('description')
        ->orWhere([ 
                    ['cost', 100, '>='],
                    ['cost', 500, '<']
        ])        
        ->get());
    }

    /*
        Showing also deleted records

        SELECT  name, cost, id FROM products WHERE (belongs_to = '90' AND (name IN ('CocaCola', 'PesiLoca')  OR cost >= 550 OR cost < 100) AND description IS NOT NULL) AND deleted_at IS NULL OR  (cost >= 100 AND cost < 500)

    */
    function where_or2(){
        Debug::dd(DB::table('products')
        ->select(['id', 'name', 'cost', 'description'])
        ->where(['belongs_to', 90])
        ->where([ 
            ['name', ['CocaCola', 'PesiLoca']], 
            ['cost', 550, '>='],
            ['cost', 100, '<']
        ], 'OR')
        ->whereNotNull('description')
        ->get());
    }

    // SELECT * FROM users WHERE (email = 'nano@g.c' OR  username = 'nano') AND deleted_at IS NULL
    function or_where3(){
        $email = 'nano@g.c';
        $username = 'nano';

        $rows = DB::table('users')->setFetchMode('ASSOC')->unhide(['password'])
            ->where([ 'email'=> $email, 
                      'username' => $username 
            ], 'OR') 
            ->setValidator((new Validator())->setRequired(false))  
            ->get();

        Debug::dd($rows);
    }

    // SELECT * FROM users WHERE (email = 'nano@g.c' OR  username = 'nano') AND deleted_at IS NULL
    function or_where3b(){
        $email = 'nano@g.c';
        $username = 'nano';

        $rows = DB::table('users')->setFetchMode('ASSOC')
            ->where([ 'email'=> $email ]) 
            ->orWhere(['username' => $username ])
            ->setValidator((new Validator())->setRequired(false))  
            ->get();

        Debug::dd($rows);
    }


    // SELECT * FROM products WHERE ((cost < IF(size = "1L", 300, 100) AND size = '1L' ) AND belongs_to = 90) AND deleted_at IS NULL ORDER BY cost ASC
    function where_raw(){
        Debug::dd(DB::table('products')
        ->where(['belongs_to' => 90])
        ->whereRaw('cost < IF(size = "1L", ?, 100) AND size = ?', [300, '1L'])
        ->orderBy(['cost' => 'ASC'])
        ->get());
    }
   
    /*
        SELECT * FROM products WHERE EXISTS (SELECT 1 FROM users WHERE products.belongs_to = users.id AND users.lastname IS NOT NULL);
    */
    function where_raw2(){
        Debug::dd(DB::table('products')->showDeleted()
        ->whereRaw('EXISTS (SELECT 1 FROM users WHERE products.belongs_to = users.id AND users.lastname = ?  )', ['AB'])
        ->get());
    }


    /*
        WHERE EXISTS

        SELECT * FROM products WHERE EXISTS (SELECT 1 FROM users WHERE products.belongs_to = users.id AND users.lastname IS NOT NULL);
    */
    function where_exists(){
        Debug::dd(DB::table('products')->showDeleted()
        ->whereExists('(SELECT 1 FROM users WHERE products.belongs_to = users.id AND users.lastname = ?)', ['AB'])
        ->get());
    }

    /*
        SELECT * FROM products WHERE 1 = 1 AND deleted_at IS NULL ORDER BY cost ASC, id DESC LIMIT 1, 4
    */
    function order(){    
        Debug::dd(DB::table('products')->orderBy(['cost'=>'ASC', 'id'=>'DESC'])->take(4)->offset(1)->get());

        Debug::dd(DB::table('products')->orderBy(['cost'=>'ASC'])->orderBy(['id'=>'DESC'])->take(4)->offset(1)->get());

        Debug::dd(DB::table('products')->orderBy(['cost'=>'ASC'])->take(4)->offset(1)->get(null, ['id'=>'DESC']));

        Debug::dd(DB::table('products')->orderBy(['cost'=>'ASC'])->orderBy(['id'=>'DESC'])->take(4)->offset(1)->get());

        Debug::dd(DB::table('products')->take(4)->offset(1)->get(null, ['cost'=>'ASC', 'id'=>'DESC']));
    }

    /*
        RAW
        
        SELECT * FROM products WHERE 1 = 1 AND deleted_at IS NULL ORDER BY locked + active ASC
    */
    function order2(){
        Debug::dd(DB::table('products')->orderByRaw('locked * active DESC')->get()); 
    }

    function grouping(){
        Debug::dd(DB::table('products')->where([ 
            ['cost', 100, '>=']
        ])->orderBy(['size' => 'DESC'])->groupBy(['size'])->select(['size'])->avg('cost'));
    }

    function where(){        

        // Ok
        Debug::dd(DB::table('products')->where([ 
            ['cost', 200, '>='],
            ['cost', 270, '<='],
            ['belongs_to',  90]
        ])->get());  
        

        /*    
        // No es posible mezclar arrays asociativos y no-asociativos 
        Debug::dd(DB::table('products')->where([ 
            ['cost', 200, '>='],
            ['cost', 270, '<='],
            ['belongs_to' =>  90]
        ])->get());
        */        

        // Ok
        Debug::dd(DB::table('products')
        ->where([ 
                ['cost', 150, '>='],
                ['cost', 270, '<=']            
            ])
        ->where(['belongs_to' =>  90])->get());         
    }
        
    function having(){  
        Debug::dd(DB::table('products')
			//->dontExec()
            ->groupBy(['size'])
            ->having(['AVG(cost)', 150, '>='])
            ->select(['size'])
			->selectRaw('AVG(cost)')
			->get());
			
		Debug::dd(DB::getQueryLog()); 
    }  

	/*
		Array
		(
			[0] => stdClass Object
				(
					[c] => 3
					[name] => Agua
				)

			[1] => stdClass Object
				(
					[c] => 5
					[name] => Vodka
				)

		)
		
		SELECT COUNT(name) as c, name FROM products WHERE deleted_at IS NULL GROUP BY name HAVING c >= 3
	*/	
	function having0(){  
        Debug::dd(DB::table('products')
			//->dontExec()
            ->groupBy(['name'])
            ->having(['c', 3, '>='])
            ->select(['name'])
			->selectRaw('COUNT(name) as c')
			->get());
			
		Debug::dd(DB::getQueryLog()); 
    }  
	
	/*
		Array
		(
			[0] => stdClass Object
				(
					[c] => 5
					[name] => Agua 
				)

			[1] => stdClass Object
				(
					[c] => 3
					[name] => Ron
				)

			[2] => stdClass Object
				(
					[c] => 9
					[name] => Vodka
				)

		)

		SELECT COUNT(name) as c, name FROM products GROUP BY name HAVING c >= 3
	*/
	function havingx(){  
        Debug::dd(DB::table('products')->showDeleted()
			//->dontExec()
            ->groupBy(['name'])
            ->having(['c', 3, '>='])
            ->select(['name'])
			->selectRaw('COUNT(name) as c')
			->get());
			
		Debug::dd(DB::getQueryLog()); 
    }  

    /*       
        En caso de tener múltiples condiciones se debe enviar un 
        array de arrays pero para una sola condición basta con enviar un simple array

        Cuando la condición es por igualdad (ejemplo: HAVING cost = 100), no es necesario
        enviar el operador "=" ya que es implícito y en este caso se puede usar un array asociativo:

            ->having(['cost' => 100])

        en vez de

            ->having(['cost', 100])

        En el caso de múltiples condiciones estas se concatenan implícitamente con "AND" excepto 
        se espcifique "OR" como segundo parámetro de having()    
    */     
	
	/*
		SELECT cost, size FROM products WHERE deleted_at IS NULL GROUP BY cost,size HAVING cost = 100
	*/
    function having1(){        
        Debug::dd(DB::table('products')
            ->groupBy(['cost', 'size'])
            ->having(['cost', 100])
            ->get(['cost', 'size']));
		
		Debug::dd(DB::getQueryLog()); 
    }    
	
	// SELECT cost, size FROM products GROUP BY cost,size HAVING cost = 100
	function having1b(){        
        Debug::dd(DB::table('products')->showDeleted()
            ->groupBy(['cost', 'size'])
            ->having(['cost', 100])
            ->get(['cost', 'size']));
		
		Debug::dd(DB::getQueryLog()); 
    }   
	
    /*
        HAVING ... OR ... OR ...

        SELECT  cost, size, belongs_to FROM products WHERE deleted_at IS NULL GROUP BY cost,size,belongs_to HAVING belongs_to = 90 AND (cost >= 100 OR size = '1L') ORDER BY size DESC
    */
    function having2(){
        Debug::dd(DB::table('products')
            ->groupBy(['cost', 'size', 'belongs_to'])
            ->having(['belongs_to', 90])
            ->having([  
                        ['cost', 100, '>='],
                        ['size' => '1L'] ], 
            'OR')
            ->orderBy(['size' => 'DESC'])
            ->get(['cost', 'size', 'belongs_to'])); 
    }

    /*
        OR HAVING
    
        SELECT  cost, size, belongs_to FROM products WHERE deleted_at IS NULL GROUP BY cost,size,belongs_to HAVING  belongs_to = 90 OR  cost >= 100 OR  size = '1L'  ORDER BY size DESC
    */
    function having2b(){
        Debug::dd(DB::table('products')
            ->groupBy(['cost', 'size', 'belongs_to'])
            ->having(['belongs_to', 90])
            ->orHaving(['cost', 100, '>='])
            ->orHaving(['size' => '1L'])
            ->orderBy(['size' => 'DESC'])
            ->get(['cost', 'size', 'belongs_to'])); 
    }

    /*
        SELECT  cost, size, belongs_to FROM products WHERE deleted_at IS NULL GROUP BY cost,size,belongs_to HAVING  belongs_to = 90 OR  (cost >= 100 AND size = '1L')  ORDER BY size DESC
    */
    function having2c(){
        Debug::dd(DB::table('products')
            ->groupBy(['cost', 'size', 'belongs_to'])
            ->having(['belongs_to', 90])
            ->orHaving([  
                        ['cost', 100, '>='],
                        ['size' => '1L'] ] 
            )
            ->orderBy(['size' => 'DESC'])
            ->get(['cost', 'size', 'belongs_to'])); 
    }

    /*
        RAW HAVING
    */
    function having3(){
        Debug::dd(DB::table('products')
            ->selectRaw('SUM(cost) as total_cost')
            ->where(['size', '1L'])
            ->groupBy(['belongs_to']) 
            ->havingRaw('SUM(cost) > ?', [500])
            ->limit(3)
            ->offset(1)
            ->get());
    }

    function joins(){
        $o = DB::table('other_permissions', 'op');
        $rows =   $o->join('folders', 'op.folder_id', '=',  'folders.id')
                    ->join('users', 'folders.belongs_to', '=', 'users.id')
                    ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
                    ->where([
                        ['guest', 1],
                        ['table', 'products'],
                        ['r', 1]
                    ])
                    ->orderByRaw('users.id DESC')
                    ->get();  
        
        Debug::dd($rows);
    }
 
    function get_nulls(){    
        // Get products where workspace IS NULL
        Debug::dd(DB::table('products')->where(['workspace', null])->get());   
   
        // Or
        Debug::dd(DB::table('products')->whereNull('workspace')->get());
    }

    /*
        Debug without exec the query
    */
    function dontexec(){
        DB::table('products')
        ->dontExec() 
        ->where([ 
                ['cost', 150, '>='],
                ['cost', 270, '<=']            
            ])
        ->where(['belongs_to' =>  90])->get(); 
        
        Debug::dd(DB::getQueryLog()); 
    }

    /*
        Pretty response 
    */
    function get_users(){
        $array = DB::table('users')->orderBy(['id'=>'DESC'])->get();

        echo '<pre>';
        Factory::response()->setPretty(true)->send($array);
        echo '</pre>';
    }

    function get_user($id){
        $u = DB::table('users');
        $u->unhide(['password']);
        $u->hide(['username', 'confirmed_email', 'firstname','lastname', 'deleted_at', 'belongs_to']);
        
        Debug::dd($u->where(['id'=>$id])->get());
    }

    function del_user($id){
        $u = DB::table('users');
        $ok = (bool) $u->where(['id' => $id])->delete(false);
        
        Debug::dd($ok);
    }

 
    function update_user($id) {
        $u = DB::table('users');

        $count = $u->where(['firstname' => 'HHH', 'lastname' => 'AAA', 'id' => 17])->update(['firstname'=>'Nico', 'lastname'=>'Buzzi', 'belongs_to' => 17]);
        
        Debug::dd($count);
    }

    function update_user2() 
    {
        $firstname = '';
        for ($i=0;$i<20;$i++)
            $firstname .= chr(rand(97,122));

        $lastname = strtoupper($firstname);    

        $u = DB::table('users');

        $ok = $u->where([ [ 'email', 'nano@'], ['deleted_at', NULL] ])
        ->update([ 
                    'firstname' => $firstname, 
                    'lastname' => $lastname
        ]);
        
        Debug::dd($ok);
    }

    function update_users() {
        $u = DB::table('users');
        $count = $u->where([ ['lastname', ['AAA', 'Buzzi']] ])->update(['firstname'=>'Nicos']);
        
        Debug::dd($count);
    }

    function create_user($email, $password, $firstname, $lastname)
     {        
        for ($i=0;$i<20;$i++)
            $email = chr(rand(97,122)) . $email;
        
        $u = DB::table('users');
        //$u->fill(['email']);
        //$u->unfill(['password']);
        $id = $u->create(['email'=>$email, 'password'=>$password, 'firstname'=>$firstname, 'lastname'=>$lastname]);
        
        Debug::dd($id);
    }

    function fillables(){
        $str = '';
        for ($i=0;$i<20;$i++)
            $str .= chr(rand(97,122));

        $p = DB::table('products');
        $affected = $p->where(['id' => 121])->update([
            'id' => 500,
            'description' => $str
        ]);

        // Show result
        $rows = DB::table('products')->where(['id' => 500])->get();
        Debug::dd($rows);
    }

    function update_products() {
        $p = DB::table('products');
        $count = $p->where([['cost', 100, '<'], ['belongs_to', 90]])->update(['description' => 'x_x']);
        
        Debug::dd($count);
    }

    function respuesta(){
        Factory::response()->sendError('Acceso no autorizado', 401, 'Header vacio');
    }
   
      // ok
    function sender(){
        Debug::dd(Utils::send_mail('boctulus@gmail.com', 'Pablo ZZ', 'Pruebita', 'Hola!<p/>Esto es una <b>prueba</b><p/>Chau'));     
    }

    function validation_test(){
        $rules = [
            'nombre' => ['type' => 'alpha_spaces_utf8', 'min'=>3, 'max'=>40],
            'username' => ['type' => 'alpha_dash', 'min'=> 3, 'max' => '15'],
            'rol' => ['type' => 'integer', 'not_in' => [2, 4, 5]],
            'poder' => ['not_between' => [4,7] ],
            'edad' => ['between' => [18, 100]],
            'magia' => [ 'in' => [3,21,81] ]
        ];
        
        $data = [
            'nombre' => 'Juan Español',
            'username' => 'juan_el_mejor',
            'rol' => 5,
            'poder' => 6,
            'edad' => 101,
            'magia' => 22
        ];

        $v = new Validator();
        Debug::dd($v->validate($rules,$data));
    }

    function validacion(){
        $u = DB::table('users');
        var_dump($u->where(['username' => 'nano_'])->get());
    }

    function validacion1(){
        $u = DB::table('users')->setValidator(new Validator());
        $affected = $u->where(['email' => 'nano@'])->update(['firstname' => 'NA']);
    }

    function validacion2(){
        $u = DB::table('users')->setValidator(new Validator());
        $affected = $u->where(['email' => 'nano@'])->update(['firstname' => 'NA']);
    }

    function validacion3(){
        $p = DB::table('products')->setValidator(new Validator());
        $rows = $p->where(['cost' => '100X', 'belongs_to' => 90])->get();

        Debug::dd($rows);
    }

    function validacion4(){
        $p = DB::table('products')->setValidator(new Validator());
        $affected = $p->where(['cost' => '100X', 'belongs_to' => 90])->delete();

        Debug::dd($affected);
    }
  
    /*
        Intento #1 de sub-consultas en el WHERE

        SELECT id, name, size, cost, belongs_to FROM products WHERE belongs_to IN (SELECT id FROM users WHERE password IS NULL);

    */
    function sub(){
        $st = DB::table('products')->showDeleted()
        ->select(['id', 'name', 'size', 'cost', 'belongs_to'])
        ->whereRaw('belongs_to IN (SELECT id FROM users WHERE password IS NULL)')
        ->get();

        Debug::dd(DB::getQueryLog());  
        Debug::dd($st);         
    }

    /*
        Intento #2 de sub-consultas en el WHERE

        SELECT id, name, size, cost, belongs_to FROM products WHERE belongs_to IN (SELECT id FROM users WHERE password IS NULL);

    */
    function sub2(){
        $sub = DB::table('users')
        ->select(['id'])
        ->whereRaw('password IS NULL');

        $st = DB::table('products')->showDeleted()
        ->select(['id', 'name', 'size', 'cost', 'belongs_to'])
        ->whereRaw("belongs_to IN ({$sub->toSql()})")
        ->get();

        Debug::dd(DB::getQueryLog());
        Debug::dd($st);            
    }

    /*
        Subconsultas en el WHERE --ok
    */
    function sub3(){
        $sub = DB::table('users')->showDeleted()
        ->select(['id'])
        ->whereRaw('confirmed_email = 1')
        ->where(['password', 100, '<']);

        $res = DB::table('products')->showDeleted()
        ->mergeBindings($sub)
        ->select(['id', 'name', 'size', 'cost', 'belongs_to'])
        ->where(['size', '1L'])
        ->whereRaw("belongs_to IN ({$sub->toSql()})")
        ->get();

        Debug::dd(DB::getQueryLog());
        Debug::dd($res);    
    }

    /*
        SELECT  id, name, size, cost, belongs_to FROM products WHERE belongs_to IN (SELECT  users.id FROM users  INNER JOIN user_roles ON users.id=user_roles.user_id WHERE confirmed_email = 1  AND password < 100 AND role_id = 2  )  AND size = '1L' ORDER BY id DESC

    */
    function sub3b(){
        $sub = DB::table('users')->showDeleted()
        ->selectRaw('users.id')
        ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
        ->whereRaw('confirmed_email = 1')
        ->where(['password', 100, '<'])
        ->where(['role_id', 2]);

        $res = DB::table('products')->showDeleted()
        ->mergeBindings($sub)
        ->select(['id', 'name', 'size', 'cost', 'belongs_to'])
        ->where(['size', '1L'])
        ->whereRaw("belongs_to IN ({$sub->toSql()})")
        ->orderBy(['id' => 'desc'])
        ->get();

        Debug::dd(DB::getQueryLog());  
        Debug::dd($res);    
    }

    function sub3c(){
        $sub = DB::table('users')->showDeleted()
        ->selectRaw('users.id')
        ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
        ->whereRaw('confirmed_email = 1')
        ->where(['password', 100, '<'])
        ->where(['role_id', 3]);

        $res = DB::table('products')->showDeleted()
        ->mergeBindings($sub)
        ->select(['size'])
        ->whereRaw("belongs_to IN ({$sub->toSql()})")
        ->groupBy(['size'])
        ->avg('cost');

        Debug::dd($res);    
    }

    /*
        RAW select

        SELECT COUNT(*)  FROM (SELECT size FROM products GROUP BY size) as sub;
    */
    function sub4(){
        $sub = DB::table('products')->showDeleted()
        ->select(['size'])
        ->groupBy(['size']);

        $conn = DB::getConnection();

        $m = new \simplerest\core\Model($conn);
        $res = $m->fromRaw("({$sub->toSql()}) as sub")->count();

        Debug::dd($res);    
    }

    /*
        SELECT  COUNT(*) FROM (SELECT  size FROM products WHERE belongs_to = 90 GROUP BY size ) as sub WHERE 1 = 1
    */
    function sub4a(){
        $sub = DB::table('products')->showDeleted()
        ->select(['size'])
        ->where(['belongs_to', 90])
        ->groupBy(['size']);

        $conn = DB::getConnection();

        $main = new \simplerest\core\Model($conn);
        $res = $main
        ->fromRaw("({$sub->toSql()}) as sub")
        ->mergeBindings($sub)
        ->count();

        Debug::dd($res); 
        Debug::dd($main->getLastPrecompiledQuery());   
    }

    /*
        FROM RAW

        SELECT  COUNT(*) FROM (SELECT  size FROM products WHERE belongs_to = 90 GROUP BY size ) as sub WHERE 1 = 1
    */
    function sub4b(){
        $sub = DB::table('products')->showDeleted()
        ->select(['size'])
        ->where(['belongs_to', 90])
        ->groupBy(['size']);

        $res = DB::table("({$sub->toSql()}) as sub")
        ->mergeBindings($sub)
        ->count();

        Debug::dd($res);    
    }
    
    /*
        Subconsulta (rudimentaria) en el SELECT
    */
    function sub5(){
        $res = DB::table('products')->showDeleted()
        ->select(['name', 'cost'])
        ->selectRaw('cost - (SELECT MAX(cost) FROM products) as diferencia')
        ->where(['belongs_to', 90])
        ->get();

        Debug::dd(DB::getQueryLog()); 
        Debug::dd($res);
    }

    /*
        UNION

        SELECT id, name, description, belongs_to FROM products WHERE belongs_to = 90 UNION SELECT id, name, description, belongs_to FROM products WHERE belongs_to = 4 ORDER by id ASC LIMIT 5;
    */
    function union(){
        $uno = DB::table('products')->showDeleted()
        ->select(['id', 'name', 'description', 'belongs_to'])
        ->where(['belongs_to', 90]);

        $dos = DB::table('products')->showDeleted()
        ->select(['id', 'name', 'description', 'belongs_to'])
        ->where(['belongs_to', 4])
        ->union($uno)
        ->orderBy(['id' => 'ASC'])
        ->limit(5)
        ->get();

        Debug::dd($dos);
    }

    function union2(){
        $uno = DB::table('products')->showDeleted()
        ->select(['id', 'name', 'description', 'belongs_to'])
        ->where(['belongs_to', 90]);

        $m2  = DB::table('products')->showDeleted();
        $dos = $m2
        ->select(['id', 'name', 'description', 'belongs_to'])
        ->where(['belongs_to', 4])
        ->where(['cost', 200, '>='])
        ->union($uno)
        ->orderBy(['id' => 'ASC'])
        ->get();

        //Debug::dd(DB::getQueryLog());
        Debug::dd($m2->getLastPrecompiledQuery());
        Debug::dd($dos);
    }

    /*
        UNION ALL
    */
    function union_all(){
        $uno = DB::table('products')->showDeleted()
        ->select(['id', 'name', 'description', 'belongs_to'])
        ->where(['belongs_to', 90]);

        $dos = DB::table('products')->showDeleted()
        ->select(['id', 'name', 'description', 'belongs_to'])
        ->where(['cost', 200, '>='])
        ->unionAll($uno)
        ->orderBy(['id' => 'ASC'])
        ->limit(5)
        ->get();

        Debug::dd($dos);
    }

    function insertar_posts()
     { 

        $posts = array (
            0 => 
            array (
              'id' => 0,
              'title' => '¿Qué es un consorcio?',
              'content' => '<div class=\'entry\'>
                        <div class=\'vc_row wpb_row vc_row-fluid mpc-row\'><div class=\'vc_span12 wpb_column vc_column_container mpc-column\' data-column-id=\'mpc_column-575e6e48db431c5\'><div class=\'vc_column-inner \'>
                  <div class=\'wpb_wrapper\'>
                      
              <div class=\'wpb_text_column wpb_content_element  \'>
                  <div class=\'wpb_wrapper\' id=\'ct_do5t3pfoht9adqo0dxmi\'>
          <p>El sector inmobiliario en la ciudad de México esta en crecimiento, la falta de espacios para vivienda se reduce por lo que vivir en consorcios es cada vez mas frecuente, pero ¿Qué es un consorcio? El inmueble que como propiedad privada reúne un derecho singular y exclusivo sobre una unidad de propiedad exclusiva (departamento, casa o local) y un derecho de copropiedad sobre elementos y partes comunes (terreno, cimientos, estructura, muros de carga, techos, azoteas, fachadas, patios, jardines, andadores, vestíbulos, escaleras y sus cubos, equipos e instalaciones, etc.).</p>
          <p><img src=\'blog/que-es-un-consorcio.jpeg\'/></p>
          <p>Los consorcios de acuerdo con sus características de estructura y uso, pueden ser:</p>
          <p>Por su estructura.</p>
          <ol><li>Condominio vertical: el inmueble edificado de forma vertical en varios niveles en un terreno común, con unidades de propiedad exclusiva y derechos de copropiedad sobre el suelo y demás elementos y partes comunes para su uso y disfrute.</li>
          <li>Condominio horizontal: el inmueble con construcción horizontal donde el consorcista tiene derecho de uso exclusivo de parte de un terreno y es propietario de la edificación establecida en el mismo, pudiendo compartir o no su estructura y medianería, siendo titular de un derecho de copropiedad para el uso y disfrute de las áreas del terreno, construcciones e instalaciones destinadas al uso común.</li>
          <li>Condominio mixto: formado por consorcios verticales y horizontales, que pueden estar constituidos en grupos de unidades de propiedad exclusiva como: edificios, cuerpos, torres, manzanas, secciones o zonas.</li>
          </ol><p>Por su uso.</p>
          <ol><li>Habitacional: aquellos en los que las unidades de propiedad exclusiva están destinadas a la vivienda.</li>
          <li>Comercial o de servicios: aquellos en los que las unidades de propiedad exclusivas están destinadas al giro o servicio que corresponda según su actividad.</li>
          <li>Industrial: aquellos en donde las unidades de propiedad exclusiva se destinan a actividades propias del ramo.</li>
          <li>Mixtos: aquellos en donde las unidades de propiedad exclusiva se destinan a dos o más de los usos señalados en los incisos anteriores.</li>
          </ol>
          
                  </div> 
              </div> 
                  </div> </div>
              </div></div>
                      </div>',
              'slug' => 'que-es-un-consorcio',
              'created_by' => NULL,
              'belongs_to' => NULL,
              'created_at' => NULL,
              'updated_at' => NULL,
            ),
            1 => 
            array (
              'id' => 1,
              'title' => '¿Qué es una unidad de propiedad privativa?',
              'content' => '<div class=\'entry\'>
                        <h2>¿ Que es una unidad de propiedad privativa ?</h2>
          <p>¿Que es una unidad de propiedad privativa? El departamento, casa o local y los elementos anexos que le corresponden sobre el cual un consorcista tiene derecho de propiedad y uso exclusivo.</p>
          <p>Dentro de esos elementos anexos se pueden encontrar: cajones para estacionamiento, cuartos de servicio, baños, jaulas para tendido, lavaderos y cualquier otro que no sea elemento común y que forme parte de su unidad de propiedad exclusiva, según la Escritura Constitutiva del Régimen de Propiedad en Condominio.<br>
          Cada consorcista, y en general los habitantes del consorcio, deben usar su unidad de propiedad exclusiva en forma ordenada y tranquila. No pueden, en consecuencia, destinarla a usos contrarios a su destino, ni hacerla servir a otros objetos que los contenidos expresamente en la Escritura Constitutiva del Régimen de Propiedad en Condominio.</p>
          <p>Por consiguiente, el consorcista, su arrendatario o cesionario del uso están obligados a usar, gozar y disponer de la unidad de propiedad exclusiva, con las limitaciones de la Ley del Régimen de Propiedad en Condominio de Inmuebles para el Distrito Federal y las demás que establecen la Escritura Constitutiva del Régimen de Propiedad en Condominio y el Reglamento Interno del Condominio.</p>
          <p>Para la venta de una unidad de propiedad exclusiva que se encuentre en arrendamiento, existen disposiciones legales sobre el derecho de preferencia para su adquisición, por lo cual es necesario informarse sobre el particular.</p>
          <p><img src=\'blog/que-es-una-unidad-de-propiedad-privativa.jpeg\'/></p>
          <p>Los consorcistas, y en general los habitantes del consorcio, al ocupar o hacer uso de sus unidades de propiedad exclusiva, deben abstenerse de:</p>
          <ol>
          <li>Realizar acto alguno que afecte la tranquilidad y comodidad de los demás consorcistas y ocupantes, o que comprometa la estabilidad, seguridad, salubridad o comodidad del consorcio, ni incurrir en omisiones que produzcan los mismos resultados.</li>
          <li>Efectuar todo acto, en el exterior o en el interior, que impida o haga ineficaz la operación de los servicios comunes e instalaciones generales, estorbe o dificulte el uso de las áreas comunes o ponga en riesgo la seguridad o tranquilidad de los consorcistas u ocupantes.</li>
          <li>Realizar obras, edificaciones, o modificaciones en el interior, como abrir claros, puertas o ventanas, entre otras, que afecten la estructura, muros de carga u otros elementos esenciales del edificio o que puedan perjudicar su estabilidad, seguridad, salubridad o comodidad.</li>
          <li>Realizar obras o reparaciones en horarios nocturnos, días festivos o inhábiles, salvo en casos de fuerza mayor y previa notificación por escrito al <strong>Administrador del consorcio</strong>, quien debe avisar al resto de los consorcistas.</li>
          <li>Decorar, pintar o realizar obras que modifiquen la fachada o las paredes exteriores desentonando con el conjunto o que contravenga lo establecido y aprobado por la Asamblea General de Condóminos.</li>
          <li>Derribar o trasplantar árboles, cambiar el uso o naturaleza de las áreas verdes en contravención a lo estipulado en la Ley Ambiental del Distrito Federal, en la Escritura Constitutiva del Régimen de Propiedad en Condominio o en el Reglamento Interno del Condominio.</li>
          <li>Delimitar o techar, con ningún tipo de material, los cajones para estacionamiento a menos que se tome el acuerdo en la Asamblea General de Condóminos.</li>
          <li>Poseer animales que por su número, tamaño o naturaleza afecten las condiciones de seguridad, salubridad o comodidad del consorcio o de los consorcistas, de acuerdo a lo que establezca el Reglamento Interno del Condominio.</li>
          </ol>
          <p>El infractor de estas disposiciones, independientemente de las sanciones que establece la Ley de Propiedad en Condominio de Inmuebles, será responsable del pago de los gastos que se efectúen para reparar o restablecer los servicios e instalaciones de que se trate, así como de los daños y perjuicios que resultaren.</p>
          <p>En los inmuebles cuya Escritura Constitutiva del Régimen de Propiedad en Condominio se establece como habitacional, no se permite ocupar las unidades de propiedad exclusiva para establecer oficinas, comercios, talleres o consultorios, debiendo tomar en cuenta y respetar esta disposición al adquirir, arrendar o vender una unidad de propiedad exclusiva.</p>
          <p>Las faltas que se cometan en las áreas comunes a que se refiere la Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal, son sancionados por la autoridad competente. Asimismo, la Procuraduría Social del Distrito Federal, a petición del <strong>Administrador o consorcista</strong> inconforme, puede intervenir en el ámbito de sus atribuciones.</p>
          <p>La realización de las obras que requieran los entrepisos, suelos, pavimentos u otras divisiones colindantes, así como su costo, son obligatorias para los consorcistas colindantes siempre y cuando la realización de la obra no derive de un daño causado por uno de los consorcistas.</p>
          <p>En los consorcios de construcción vertical, las obras que requieran los techos en su parte exterior y los sótanos son por cuenta de todos los consorcistas.</p>
          <p>SI TE INTERESA CONOCER MAS DE NUESTRO SERVICIO VISITA <a href=\'https://www.administracionelgrove.com\'>ADMINISTRACIÓN DE CONSORCIOS</a></p>
                      </div>',
              'slug' => 'que-es-una-unidad-de-propiedad-privativa',
              'created_by' => NULL,
              'belongs_to' => NULL,
              'created_at' => NULL,
              'updated_at' => NULL,
            ),
            2 => 
            array (
              'id' => 2,
              'title' => '¿Cuáles son las áreas y bienes de propiedad común?',
              'content' => '<div class=\'entry\'>
                        <h2>¿Cuáles son las áreas y bienes de propiedad común?</h2>
          <p>Son aquellas partes, instalaciones y equipos del consorcio que pertenecen en forma proindiviso a los consorcistas y su uso está regulado por la <a href=\'http://www.aldf.gob.mx/vivienda/media/01_ley_propiedad_consorcio_df-2.pdf\'>Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal</a>, la Escritura Constitutiva del Régimen de Propiedad en Condominio y el Reglamento Interno del Condominio: ¿Cuáles son las áreas y bienes de propiedad común?</p>
          <ol>
          <li>El terreno, cimientos, estructuras, sótanos, muros de carga, techos y azoteas de uso general.</li>
          <li>Las puertas de entrada al consorcio, vestíbulos, galerías, corredores, escaleras, patios, jardines, plazas, senderos, calles interiores, instalaciones deportivas, de recreo, de recepción o reunión social y los espacios señalados para estacionamiento de vehículos, siempre que dichas áreas sean de uso general.</li>
          <li>Los locales destinados a la administración, personal a cargo de la administración, conserje, vigilantes, bodega para artículos y enseres de limpieza y taller de mantenimiento, más los destinados a las instalaciones y equipos para servicios comunes.</li>
          <li>Los locales y las obras de seguridad, de ornato, y zonas de carga en lo general, y otras semejantes, con excepción de los que sirvan exclusivamente a cada unidad de propiedad exclusiva;</li>
          <li>Las obras, instalaciones, aparatos y demás equipos que sirvan de uso o disfrute común, tales como cuartos de máquinas, fosas, pozos, cisternas, tinacos, elevadores, montacargas, incineradores, estufas, hornos, bombas y motores; albañales, canales, conductos de distribución de agua, drenaje, calefacción, electricidad y gas.</li>
          <li>Los entrepisos, muros y demás divisiones que compartan entre sí las unidades de propiedad exclusiva colindantes.</li>
          <li>Otras partes del consorcio establecidas con tal carácter en la Escritura Constitutiva del Régimen de Propiedad en Condominio y en el Reglamento Interno del Condominio.</li>
          </ol>
          <p><img src=\'blog/cuales-son-las-areas-y-bienes-de-propiedad-comun-300x156.jpg\'/></p>
          <h2>Respetar la áreas comunes</h2>
          <p>Es importante su respeto a las áreas y bienes comunes del consorcio, así como su correcto uso, lo cual le evitará diferencias con sus vecinos y ser reconvenido por el Administrador o el Comité de Vigilancia.</p>
          <p>Ningún ocupante del consorcio puede obstaculizar o colocar objetos que dificulten el tránsito, uso, atenten contra la seguridad o signifiquen pretensión de ejercer dominio sobre las áreas o bienes comunes.</p>
          <p>En los consorcios verticales, ningún consorcista independientemente de la ubicación de su unidad de propiedad exclusiva tiene más derechos que el resto de los consorcistas.</p>
          <p>Salvo que lo establezca el Reglamento Interno del Condominio, los consorcistas de la planta baja no pueden hacer obras ni ocupar para su uso exclusivo o preferente sobre los demás consorcistas en los vestíbulos, sótanos, jardines, patios u otros espacios de esa planta considerados como comunes, incluidos los destinados a cubos de luz.</p>
          <p>Así mismo, los consorcistas del último piso no pueden ocupar las azoteas ni elevar nuevas construcciones.</p>
          <p>Las mismas restricciones son aplicables a los demás consorcistas.</p>
          <h2>La copropiedad en el consorcio</h2>
          <p>El derecho de copropiedad sobre los elementos comunes del consorcio es accesorio e indivisible del derecho de propiedad privativo sobre la unidad de propiedad exclusiva, por lo que no pueden ser enajenados, gravados o embargados separadamente de la misma unidad.<br>
          Las construcciones en áreas comunes están prohibidas. Aún más, no puede cambiarse su destino original a pesar de que la obra mejore el aspecto o dé mayor comodidad al consorcio. Quien infrinja esta disposición puede hacerse acreedor a una multa por la Procuraduría Social del Distrito Federal.</p>
          <p>En el régimen de consorcio la práctica de construcciones en las áreas comunes es la segunda mayor incidencia que se presenta en el Distrito Federal, por lo cual se ha legislado rigurosamente al establecerse multas con altas cuantías, que vienen a ser medidas adicionales a los procedimientos civiles que correspondan.</p>
          <p>Contra los causantes de invasión o construcción en áreas comunes, el Administrador del consorcio puede solicitar la intervención de la Procuraduría Social del Distrito Federal, quien impondrá la correspondiente multa al infractor.</p>
          <p>Asimismo, el Administrador del consorcio está facultado para entablar un juicio de orden civil por invasión de áreas comunes y otro de orden penal por despojo.</p>
          <p> </p>
          <h2><strong>Estacionamiento del consorcio bien de propiedad </strong>común</h2>
          <p>El estacionamiento del consorcio es un aspecto conflictivo en el régimen condominal. En principio, recomendamos que revise su Escritura de compraventa para cerciorarse si el cajón o cajones para estacionamiento que le corresponden le fueron vendidos como parte de la propiedad exclusiva o solamente tiene el derecho de uso de uno o más cajones para estacionamiento en las áreas comunes:</p>
          <p>En el primer caso, nadie a excepción del propietario, su arrendatario o cesionario del uso de la unidad de propiedad privada puede usar el o los cajones para estacionamiento.</p>
          <p>En el segundo caso, de no existir en la Escritura Constitutiva del Régimen de Propiedad en Condominio, en el Reglamento Interno del Condominio o en la Escritura de compraventa algún señalamiento del número de vehículos que pueden accesar o la identificación del cajón o cajones para estacionamiento que le corresponden en las áreas comunes, el consorcista puede estacionar su vehículo o vehículos en cualquier espacio dentro del área destinada para tal fin. Sin embargo, suele haber acuerdos de Asamblea que han reglamentado el uso de los espacios por lo que los consorcistas deben acatar las disposiciones respectivas.</p>
          <p>Si el estacionamiento que le corresponde está dentro del área común y es ocupado por otro vecino, comuníquelo al Administrador para que él resuelva en consecuencia, recuerde que es su función. Los consorcistas, sus arrendatarios o cesionarios del uso deberán respetar la asignación, estado y uso original de los cajones para estacionamiento y, por consiguiente, se abstendrán de:</p>
          <ol>
          <li>Ocupar cajones ajenos.</li>
          <li>Estacionarse incorrectamente.</li>
          <li>Obstruir la entrada o salida de otros vehículos.</li>
          <li>Realizar construcciones de cualquier tipo, delimitarlos con jaulas, postes o strs.</li>
          <li>Señalizarlos o identificarlos de manera personalizada.</li>
          <li>Almacenar o colocar objetos de manera permanente o momentánea sin importar su naturaleza.</li>
          <li>Efectuar modificaciones en las instalaciones eléctricas, hidro-sanitarias, de iluminación o ventilación.</li>
          </ol>
          <p>Aunque es una práctica común la venta o el arrendamiento de los cajones para estacionamiento cuando forman parte de la unidad de propiedad exclusiva ello va contra la Ley, por lo que se recomienda evitarse molestias al contravenir esta disposición.</p>
          <p>Artículo 21.- X. Queda prohibido a los consorcistas, poseedores y en general a toda persona y habitantes del consorcio: Ocupar otro cajón de estacionamiento distinto al asignado; para este artículo se aplicará de manera supletoria la Ley de cultura Cívica del Distrito Federal y demás leyes aplicables.</p>
          <p>SI TE INTERESA CONOCER MAS DE NUESTRO SERVICIO VISITA <a href=\'https://www.administracionelgrove.com\'>ADMINISTRACIÓN DE CONSORCIOS</a></p>
                      </div>',
              'slug' => 'cuales-son-las-areas-y-bienes-de-propiedad-comun',
              'created_by' => NULL,
              'belongs_to' => NULL,
              'created_at' => NULL,
              'updated_at' => NULL,
            ),
            3 => 
            array (
              'id' => 3,
              'title' => '¿Quién es un consorcista?',
              'content' => '<div class=\'entry\'>
                        <h2><strong>¿Quien es un consorcista según la Ley de propiedad en consorcio?</strong></h2>
          <p>Según la <a href=\'http://www.aldf.gob.mx/vivienda/media/01_ley_propiedad_consorcio_df-2.pdf\'>ley de propiedad  en consorcio de inmuebles para el Distrito Federal</a>, quien es un consorcista es la Persona física o moral, propietaria de una o más unidades de propiedad privativa y, para los efectos de esta Ley, y su Reglamento, a la que haya celebrado contrato en virtud del cual, de cumplirse en sus términos, llegue a ser propietario bajo el régimen de propiedad en consorcio.</p>
          <h2>¿Que es un consorcista <strong>según la Ley de propiedad en consorcio</strong>?</h2>
          <p>Es la persona física o moral que en calidad de propietario esté en posesión de una o más unidades de propiedad exclusiva del consorcio, y la que haya celebrado contrato en virtud del cual, de cumplirse en sus términos, llegue a ser propietario, quien usará, gozará y dispondrá de ella con las limitaciones y prohibiciones establecidas por el Artículo novecientos cincuenta y uno del Código Civil para el Distrito Federal, la Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal, la Escritura Constitutiva del Régimen de Propiedad en Condominio y el Reglamento Interno del Condominio.</p>
          <p><strong><img src=\'blog/que-es-un-consorcista.jpg\'/></strong></p>
          <p> </p>
          <p>Es importante mencionar que un consorcista tiene cierto porcentaje de la unidad total. Es decir tiene un parte del bien, el consorcista que cuente con mayor porcentaje  mayor responsabilidad tendrá.</p>
          <h2><strong>¿Que puede hacer el consorcista?</strong></h2>
          <p>El consorcista puede arrendar, enajenar, gravar y ejercer cualquier acto de dominio sobre su propiedad, con las limitaciones establecidas por la Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal, la Escritura Constitutiva del Régimen de Propiedad en Condominio y el Reglamento Interno del Condominio.</p>
          <p>Es recomendable que al arrendar o ceder el uso de sus unidades de propiedad exclusiva, los consorcistas procedan a notificar por escrito al <strong>Administrador del consorcio</strong> para que no se dé la ocupación o se permita la desocupación sin su conocimiento y autorización.</p>
          <h2><strong><img src=\'blog/quien-es-un-consorcista.jpg\'/></strong></h2>
          <h2><strong>¿ Quien es un consorcista puede ser administrador?</strong></h2>
          <p>Afirmativo, El <strong>administrador consorcista</strong> que tendrá que ser nombrado por la Asamblea General.</p>
          <p>Si necesitas mayor información puedes consultar cualquier duda con alguno de nuestros consultores en:</p>
          <p>SI TE INTERESA CONOCER MAS DE NUESTRO SERVICIO VISITA <a href=\'https://www.administracionelgrove.com\'>ADMINISTRACIÓN DE CONSORCIOS</a></p>
          <p> </p>
          <p> </p>
                      </div>',
              'slug' => 'quien-es-un-consorcista',
              'created_by' => NULL,
              'belongs_to' => NULL,
              'created_at' => NULL,
              'updated_at' => NULL,
            ),
            4 => 
            array (
              'id' => 4,
              'title' => '¿Quién es un residente?',
              'content' => '<div class=\'entry\'>
                        <h2>¿ Quién es un residente del consorcio ?</h2>
          <p>¿Quién es un residente? El Residente es la persona que en calidad de poseedor por cualquier título legal, aproveche en su beneficio una unidad de propiedad exclusiva. El consorcista, su arrendatario o cualquier otro cesionario del uso, así como sus familiares, que habitan de manera regular el consorcio y tiene el derecho singular y exclusivo de las unidades de propiedad exclusiva y el derecho de copropiedad sobre los elementos y partes del consorcio que se consideran comunes en la Escritura Constitutiva del Régimen de Propiedad en Condominio y el Reglamento Interno del Condominio.</p>
          <p><img src=\'blog/quien-es-un-residente-300x141.jpg\'/></p>
          <h2>¿Quien paga las cuotas el arrendatario o arrendador?</h2>
          <p>Debiendo acordarse entre ellos sobre quién cumplirá con el pago de las cuotas de <a href=\'https://www.prosoc.com.mx/prosoc-administracion-condominal/\'>administración condominal</a> y mantenimiento y otras aportaciones a favor del consorcio, así como sobre la representación del consorcista en las Asambleas Generales de Condóminos que se celebren, pero en todo momento el consorcista es solidario responsable de esas obligaciones.</p>
          <p>Oportunamente y por escrito deben hacer las notificaciones correspondientes al Administrador dentro de los primeros cinco días hábiles, para los efectos que procedan.</p>
          <p>SI TE INTERESA CONOCER MAS DE NUESTRO SERVICIO VISITA <a href=\'https://www.administracionelgrove.com\'>ADMINISTRACIÓN DE CONSORCIOS</a></p>
                      </div>',
              'slug' => 'quien-es-un-residente',
              'created_by' => NULL,
              'belongs_to' => NULL,
              'created_at' => NULL,
              'updated_at' => NULL,
            ),
            5 => 
            array (
              'id' => 5,
              'title' => '¿Cuáles son las disposiciones legales que regulan al consorcio?',
              'content' => '<div class=\'entry\'>
                        <h2><strong>¿Cuáles son las disposiciones legales que regulan al consorcio en el Distrito Federal?</strong></h2>
          <p>En la Ciudad de México las disposiciones legales que regular al consorcio son:</p>
          <ol>
          <li>La Escritura Constitutiva del Régimen de Propiedad en Condominio.</li>
          <li>El Reglamento Interno del Condominio.</li>
          <li>Los acuerdos de la Asamblea General de Condóminos.</li>
          <li>La <a href=\'http://www.aldf.gob.mx/vivienda/media/01_ley_propiedad_consorcio_df-2.pdf\'>Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal</a></li>
          </ol>
          <p><img src=\'blog/cuales-son-las-disposiciones-legales-que-regulan-al-consorcio.jpg\'/></p>
          <p>SI TE INTERESA CONOCER MAS DE NUESTRO SERVICIO VISITA <a href=\'https://www.administracionelgrove.com\'>ADMINISTRACIÓN DE CONSORCIOS</a></p>
                      </div>',
              'slug' => 'cuales-son-las-disposiciones-legales-que-regulan-al-consorcio-2',
              'created_by' => NULL,
              'belongs_to' => NULL,
              'created_at' => NULL,
              'updated_at' => NULL,
            ),
            6 => 
            array (
              'id' => 6,
              'title' => '¿Cuál es la documentación indispensable del consorcio?',
              'content' => '<div class=\'entry\'>
                        <h2>¿Cuál es la documentación indispensable del consorcio?</h2>
          <p>¿Cuál es la documentación indispensable del consorcio? En la oficina de la administración o lugar determinado para tal fin, deberán conservarse de manera ordenada, en buen estado y de fácil acceso a los consorcistas o autoridades que los requieran, los siguientes documentos.</p>
          <p><img src=\'blog/cual-es-la-documentacion-indispensable-del-consorcio-300x115.jpg\'/></p>
          <p> </p>
          <ol>
          <li>Escritura Constitutiva del Régimen de Propiedad en Condominio, con sus anexos.</li>
          <li>Reglamento Interno del Condominio.</li>
          <li>Planos estructurales, arquitectónicos, de instalaciones y equipos del consorcio.</li>
          <li>Acuses de recibo de los consorcistas de las convocatorias a Asambleas.</li>
          <li>Libro de actas de Asambleas Generales de Condóminos.</li>
          <li>Informes de los Comités de Vigilancia.</li>
          <li>Archivos contables y legales.</li>
          <li>Estados de cuenta bancarios del consorcio.</li>
          <li>Facturas, bitácoras de servicio y garantías de instalaciones y equipos del consorcio.</li>
          <li>Actas circunstanciadas de la entrega-recepción de los administradores.</li>
          <li>Inventario actualizado de los muebles, aparatos, equipos e instalaciones de propiedad común.</li>
          <li>Registro del consorcio en la <a href=\'https://www.prosoc.com.mx\'>PROSOC</a></li>
          </ol>
          <p>SI TE INTERESA CONOCER MAS DE NUESTRO SERVICIO VISITA <a href=\'https://www.administracionelgrove.com\'>ADMINISTRACIÓN DE CONSORCIOS</a></p>
                      </div>',
              'slug' => 'cual-es-la-documentacion-indispensable-del-consorcio',
              'created_by' => NULL,
              'belongs_to' => NULL,
              'created_at' => NULL,
              'updated_at' => NULL,
            ),
            7 => 
            array (
              'id' => 7,
              'title' => '¿Cuál es la documentación que debe tener un consorcista?',
              'content' => '<div class=\'entry\'>
                        <h2><strong>¿Cuál es la documentación que debe tener un consorcista?</strong></h2>
          <p>¿Cuál es la documentación que debe tener un consorcista? Cuando se adquiere una unidad de propiedad exclusiva deben recibirse los documentos que acrediten su propiedad y posesión jurídica. Además de las constancias que confirmen que se encuentra libre de todo tipo de gravámenes.</p>
          <p>Si bien la Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal no precisa quien debe entregar la documentación, se sugiere que el consorcista la solicite al Notario Público que se encarga de la escrituración.</p>
          <h2>¿Que documentos hay que verificar al comprar un inmueble en consorcio?</h2>
          <p>Además, es importante exigir al vendedor de un inmueble en consorcio, que cumpla con la obligación de entregar una constancia de no adeudo de cuotas de mantenimiento y administración, del fondo de reserva y por otras aportaciones con cargo a la unidad de propiedad exclusiva, expedida por el Administrador del consorcio y avalada por el Comité de Vigilancia, ya que de otra manera se convierte en deudor solidario de las cuotas o aportaciones dejadas de pagar.</p>
          <p><img src=\'blog/cual-es-la-documentacion-que-debe-tener-un-consorcista-300x160.jpg\'/></p>
          <p>En cuanto tenga la documentación legal y suficiente para ello, le sugerimos que realice el cambio de propietario en las cuentas del impuesto predial y por consumo de agua, así como las correspondientes a gas, luz y teléfono. Esta acción le evitará situaciones incomodas y aclaraciones innecesarias.</p>
          <p>Sobre el particular puede solicitar y recibir el apoyo del Administrador del consorcio, quien en su nombre y representación puede efectuar los trámites correspondientes.</p>
          <p>Tenga presente que el Administrador no es un simple recaudador de las cuotas y aportaciones a favor del consorcio, él puede y debe auxiliarlo en gestiones y trámites inherentes a su unidad de propiedad exclusiva ante las autoridades y dependencias oficiales.</p>
          <p><img src=\'blog/cual-es-la-documentacion-que-debe-tener-un-consorcista-1-300x166.jpg\'/></p>
          <h2>¿Que documentos debe de tener un consorcista referente a su propiedad privativa?</h2>
          <ol>
          <li>Escritura traslativa de dominio (Escritura de compraventa).</li>
          <li>Copia de la Escritura Constitutiva del Régimen de Propiedad en Condominio.</li>
          <li>Copia de la Tabla de Valores e Indivisos.</li>
          <li>Copia del Reglamento Interno del Condominio.</li>
          <li>Constancias de libertad de gravámenes y no adeudo.</li>
          <li>Recibos de pagos por impuesto predial y consumo de agua.</li>
          <li>Recibos de pagos por cuotas de mantenimiento y administración y de fondo de reserva, así como por otras aportaciones.</li>
          <li>Constancia de no adeudo de cuotas de administración y mantenimiento, del fondo de reserva y por aportaciones con cargo a la unidad de propiedad exclusiva, expedida por el Administrador.</li>
          <li><a href=\'http://www.aldf.gob.mx/vivienda/media/01_ley_propiedad_consorcio_df-2.pdf\'>Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal</a>.</li>
          <li>Lea con detenimiento su Escritura de compraventa y la Escritura Constitutiva del Régimen de Propiedad en Condominio, verificando la coincidencia entre ambos documentos sobre todo lo relacionado a su unidad de propiedad exclusiva; área, medidas, colindancias, indiviso y uso autorizado, entérese de cuáles son las áreas y bienes comunes del inmueble donde usted ha invertido.</li>
          </ol>
          <div id=\'attachment_897\' style=\'width: 286px\' class=\'wp-caption alignnone\'><img src=\'blog/cual-es-la-documentacion-que-debe-tener-un-consorcista.jpeg\'/><p id=\'caption-attachment-897\' class=\'wp-caption-text\'>¿Cuál es la documentación que debe tener un condómino?</p></div>
          <p>SI TE INTERESA CONOCER MAS DE NUESTRO SERVICIO VISITA <a href=\'https://www.administracionelgrove.com\'>ADMINISTRACIÓN DE CONSORCIOS</a></p>
                      </div>',
              'slug' => 'cual-es-la-documentacion-que-debe-tener-un-consorcista',
              'created_by' => NULL,
              'belongs_to' => NULL,
              'created_at' => NULL,
              'updated_at' => NULL,
            ),
            8 => 
            array (
              'id' => 8,
              'title' => '¿Qué es la escritura constitutiva del régimen de propiedad en consorcio?',
              'content' => '<div class=\'entry\'>
                        <h2><strong>¿Qué es la escritura constitutiva del régimen de propiedad en consorcio?</strong></h2>
          <p>¿Qué es la escritura constitutiva del régimen de propiedad en consorcio? El instrumento jurídico que contiene toda la información del inmueble en consorcio.</p>
          <p><img src=\'blog/la-escritura-constitutiva-del-regimen-de-propiedad-en-consorcio.jpg\'/></p>
          <p>En otras palabras, es su acta de nacimiento que señala con toda claridad lo siguiente:</p>
          <h3>Ubicación del consorcio</h3>
          <p>Se incluyen dentro de la escritura constitutiva dimensiones, medidas y colindancias.</p>
          <h3>Antecedentes de propiedad</h3>
          <p>Incluye las licencias y autorizaciones que hacen constar su cumplimiento con las disposiciones legales vigentes al momento de su constitución</p>
          <h3>Descripción de áreas privativas y comunes</h3>
          <p>La descripción de cada unidad de propiedad exclusiva, su número, ubicación, uso autorizado, colindancias y medidas; la descripción de las áreas y bienes comunes, su destino, especificaciones, ubicación, medidas, componentes y todos aquellos datos que permiten su fácil identificación.</p>
          <h3>El Valor de cada unidad</h3>
          <p>El valor asignado a cada unidad de propiedad exclusiva y su porcentaje indiviso en relación al total del inmueble condominal.</p>
          <p>La escritura constitutiva del régimen de propiedad en consorcio es una escritura pública y debe estar inscrito en el <a href=\'https://www.gob.mx/indaabin/articulos/registro-publico-de-la-propiedad-federal?idiom=es\'>Registro Público de la Propiedad</a> y corresponde al propietario original del consorcio el cumplimiento de las formalidades legales.</p>
          <p>SI TE INTERESA CONOCER MAS DE NUESTRO SERVICIO VISITA <a href=\'https://www.administracionelgrove.com\'>ADMINISTRACIÓN DE CONSORCIOS</a></p>
                      </div>',
              'slug' => 'que-es-la-escritura-constitutiva-del-regimen-de-propiedad-en-consorcio',
              'created_by' => NULL,
              'belongs_to' => NULL,
              'created_at' => NULL,
              'updated_at' => NULL,
            ),
            9 => 
            array (
              'id' => 9,
              'title' => '¿Qué es el reglamento interno del consorcio?',
              'content' => '<div class=\'entry\'>
                        <h2><strong>¿Qué es el reglamento interno del consorcio?</strong></h2>
          <p>¿Qué es el reglamento interno del consorcio? También es un instrumento jurídico que complementa y especifica las disposiciones de la <a href=\'http://www.aldf.gob.mx/vivienda/media/01_ley_propiedad_consorcio_df-2.pdf\'>Ley de Propiedad en Condominio para Inmuebles del Distrito Federal</a>, de acuerdo a las características propias de cada consorcio.</p>
          <p><img src=\'blog/que-es-el-reglamento-interno-del-consorcio-300x141.jpg\'/></p>
          <p>El Reglamento Interno del Condominio señala las normas a que deben sujetarse los ocupantes del consorcio. Cada consorcio tiene sus propias características y por lo tanto sus propias disposiciones. Su elaboración puede tener dos orígenes:</p>
          <h3>Elaboración del reglamento interno del consorcio</h3>
          <ul>
          <li>Para construcciones nuevas, por quienes otorgan la Escritura Constitutiva del Régimen de Propiedad en Condominio, quedando sujeta su ratificación a la Asamblea General de Condóminos.</li>
          <li>Para inmuebles en uso, por los consorcistas y aprobado en Asamblea General de Condóminos.</li>
          </ul>
          <h2>¿Que debe incluir el reglamento interno del consorcio?</h2>
          <ol>
          <li>Los derechos, obligaciones y limitaciones a que quedan sujetos los consorcistas en el ejercicio del derecho de usar las áreas y bienes comunes y los propios.</li>
          <li>El tipo de administración conforme a lo establecido en el Artículo 37° de la Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal.</li>
          <li>Las políticas para la administración, mantenimiento y operación del consorcio.</li>
          <li>Las aportaciones para la constitución de los fondos de administración y mantenimiento y de reserva.</li>
          <li>El monto y la periodicidad del cobro de las cuotas de administración y mantenimiento, fondo de reserva, así como de las cuotas extraordinarias.</li>
          <li>El procedimiento para el cobro de las cuotas de administración y mantenimiento, fondo de reserva, así como de las cuotas extraordinarias.</li>
          <li>Las disposiciones para propiciar la integración, organización y desarrollo de la comunidad.</li>
          <li>El tipo de Asambleas que se realizarán de acuerdo a lo establecido en el Artículo 31° de la Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal.</li>
          <li>Los criterios generales a los que se sujetará el Administrador para la contratación a terceros de locales, espacios o instalaciones de propiedad común que sean objeto de arrendamiento o comodato.</li>
          <li>Otras obligaciones y requisitos para el Administrador y los miembros del Comité de Vigilancia, además de lo establecido por la Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal.</li>
          <li>Causas para la remoción o rescisión del contrato de servicios del Administrador y de los miembros del Comité de Vigilancia.</li>
          <li>El establecimiento de medidas provisionales en los casos de ausencia temporal del Administrador.</li>
          <li>Las bases para la modificación del Reglamento Interno del Condominio conforme a lo establecido en la Escritura Constitutiva del Régimen de Propiedad en Condominio.</li>
          <li>La determinación de criterios para el uso de las áreas comunes, especialmente para aquéllas que deban destinarse exclusivamente a personas con discapacidad, ya sean consorcistas o familiares que habiten con ellos.</li>
          <li>Determinar, en su caso, las medidas y limitaciones para poseer animales en las unidades de propiedad exclusiva o áreas comunes.</li>
          <li>La determinación de criterios para asuntos que requieran una mayoría especial en caso de votación y no previstos en la Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal.</li>
          <li>Las bases para la integración del Programa Interno de Protección Civil. Así como, en su caso, la conformación de Comités de Protección Civil y de Seguridad Pública.</li>
          <li>Las materias que le reservan la Escritura Constitutiva del Régimen de Propiedad en Condominio y la Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal.</li>
          <li>El Reglamento Interno del Condominio forma parte del apéndice de la Escritura Constitutiva del Régimen de Propiedad en Condominio, conforme a lo establecido en el último párrafo del Artículo 10 de la Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal.</li>
          </ol>
          <p><img src=\'blog/que-es-el-reglamento-interno-del-consorcio-1.jpg\'/></p>
          <h2>¿Cómo debe registrarse el Reglamento interno del consorcio?</h2>
          <p>Asimismo, el Reglamento Interno del Condominio debe registrarse ante la Procuraduría Social del Distrito Federal, que previamente verifica que no contravenga las disposiciones establecidas en la Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal y la Escritura Constitutiva del Régimen de Propiedad en Condominio.</p>
          <p>El Reglamento Interno del Condominio es susceptible de modificación o actualización por acuerdo de la Asamblea General de Condóminos, cumpliéndose con las formalidades que al respecto establece la Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal.</p>
          <h2>Adecuaciones al reglamento interno del consorcio</h2>
          <p>Nuestra experiencia indica que en algunos consorcios ha sido necesario crear o modificar el reglamento y adecuarlo a la vida diaria del consorcio con el fin de contar con instrumentos que obliguen a los consorcistas a cumplir con sus obligaciones condominales.</p>
          <p>Al aceptar nuestra propuesta, será conveniente revisar este instrumento y darles las observaciones para el caso al Comité de Vigilancia.</p>
          <p>SI TE INTERESA CONOCER MAS DE NUESTRO SERVICIO VISITA <a href=\'https://www.administracionelgrove.com\'>ADMINISTRACIÓN DE CONSORCIOS</a></p>
                      </div>',
              'slug' => 'que-es-el-reglamento-interno-del-consorcio',
              'created_by' => NULL,
              'belongs_to' => NULL,
              'created_at' => NULL,
              'updated_at' => NULL,
            ),
            10 => 
            array (
              'id' => 10,
              'title' => '¿Qué es la asamblea general de consorcistas?',
              'content' => '<div class=\'entry\'>
                        <h2>¿Qué es la asamblea general de consorcistas en un consorcio en la CDMX?</h2>
          <p>¿Qué es la asamblea general de consorcistas? La Asamblea General de Condóminos es el órgano supremo y máxima autoridad del consorcio en donde con reunión de todos los titulares propietarios o que estén en proceso de serlo, celebrada previa convocatoria, se tratan, discuten y resuelven, en su caso, asuntos de interés común.</p>
          <p><img src=\'blog/que-es-la-asamblea-general-de-consorcistas.jpg\'/></p>
          <p>De acuerdo a la <a href=\'http://www.aldf.gob.mx/vivienda/media/01_ley_propiedad_consorcio_df-2.pdf\'>Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal</a>, las Asambleas Generales de Condóminos por su tipo pueden ser ordinarias y extraordinarias:</p>
          <p>I. Las ordinarias se celebran cada seis meses teniendo como finalidad informar el estado que guarda la administración del consorcio, así como tratar los asuntos concernientes al mismo; y</p>
          <p>II. Las extraordinarias se celebran cuando hay asuntos de carácter urgente que atender y para:</p>
          <ol>
          <li>La modificación de la Escritura Constitutiva del Régimen de Propiedad en Condominio o del Reglamento Interno del Condominio.</li>
          <li>La extinción voluntaria del Régimen de Propiedad en Condominio.</li>
          <li>La ejecución de obras nuevas en el consorcio.</li>
          <li>Acordar lo conducente en caso de destrucción, ruina o reconstrucción del consorcio.</li>
          </ol>
          <p>El asistir y participar en asambleas te permite tomar decisiones y acuerdos de interés común, el Articulo 32 de la Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal, Indica que; La Asamblea General es el órgano del consorcio, que constituye la máxima instancia en la toma de decisiones para expresar, discutir y resolver asuntos de interés propio y común, celebrada en los términos de la presente Ley, su Reglamento, Escritura Constitutiva y el Reglamento Interno. (Reforma del 3 de abril de 2012)</p>
          <p><img src=\'blog/que-es-la-asamblea-general-de-consorcistas1.jpg\'/></p>
          <p>Es decir, los consorcistas se reúnen para la toma de decisiones a favor del consorcio, esta reunión también sirve para informar sobre la gestión del Administrador, el balance financiero, los presupuestos anuales de los gastos a efectuar, y demás asuntos generales del consorcio.</p>
          <p>La participación en las asambleas es uno de los derechos otorgados por la Ley condominal de la ciudad de México, pero también es una de las obligaciones más importantes dentro de la cultura condominal, la participación hace la diferencia.</p>
          <p>Art. 16 Fracc. II: Participar con voz y voto en las asambleas generales de condóminios.</p>
          <p>Art. 16 Las Asambleas Generales por su tipo podrán ser ordinarias y extraordinarias.</p>
          <p><img src=\'blog/que-es-la-asamblea-general-de-consorcistas-2.jpg\'/>¿Qué es la asamblea general de consorcistas?</p>
          <p> </p>
          <p><span style=\'font-size: 16px;\'>Las Asambleas Generales Ordinarias: Se celebrarán cuando menos cada seis meses teniendo como finalidad informar el estado que guarda la administración del consorcio, así como tratar los asuntos concernientes al mismo:</span></p>
          <ul>
          <li>Las Asambleas Generales Extraordinarias: Se celebrarán cuando haya asuntos de carácter urgente que atender y cuando se trate de los siguientes asuntos:</li>
          <li>Cualquier modificación a la escritura constitutiva del consorcio o su Reglamento Interno de conformidad con lo establecido en esta Ley:</li>
          <li>Para la extinción voluntaria del Régimen;</li>
          <li>Para realizar obras nuevas;</li>
          <li>Para acordar lo conducente en caso de destrucción, ruina o reconstrucción.</li>
          </ul>
          <p>SI TE INTERESA CONOCER MAS DE NUESTRO SERVICIO VISITA <a href=\'https://www.administracionelgrove.com\'>ADMINISTRACIÓN DE CONSORCIOS</a></p>
                      </div>',
              'slug' => 'que-es-la-asamblea-general-de-consorcistas',
              'created_by' => NULL,
              'belongs_to' => NULL,
              'created_at' => NULL,
              'updated_at' => NULL,
            ),
            11 => 
            array (
              'id' => 11,
              'title' => '',
              'content' => '',
              'slug' => 'consorcio',
              'created_by' => NULL,
              'belongs_to' => NULL,
              'created_at' => NULL,
              'updated_at' => NULL,
            ),
            12 => 
            array (
              'id' => 12,
              'title' => 'ADMINISTRADORA DE CONSORCIOS',
              'content' => '<div class=\'entry\'>
                        <h2><strong>ADMINISTRADORA DE CONSORCIOS EN LA CIUDAD DE MÉXICO</strong></h2>
          <p>Una persona física (consorcista) ó persona moral pueden tener la representación administradora de consorcios, a continuación conoce cual es son sus funciones y el procedimiento para el nombramiento en el Distrito Federal.</p>
          <h2><strong>¿QUIEN ES EL ADMINISTRADOR DE CONSORCIOS?</strong></h2>
          <p>El administrador de consorcios puede ser una una persona física o moral, la <strong>empresa administradora de consorcios</strong> o la persona física es nombrada por la <strong>Asamblea General</strong>, para llevar a cabo la <strong>administración de un inmueble</strong> sujeto al <strong>Régimen de Propiedad en Condominio</strong>.</p>
          <p><strong>La administradora de consorcios</strong> o persona física pueden ser de dos tipos:</p>
          <p><strong>Administrador de consorcio</strong>: Es aquel administrador que es un consorcista y es nombrado por la <strong>Asamblea General</strong>.</p>
          <p><strong>Administrador Profesional</strong>: Es la persona física o moral que demuestre capacidad y conocimientos en administración de consorcios que es contratado por la Asamblea General.</p>
          <h2><strong>¿ COMO SE ELIGE A LA EMPRESA ADMINISTRADORA DE CONSORCIOS ?</strong></h2>
          <p>Ambos tipos de administradores  (profesional ó condominal) se eligen por mayoría simple en <strong>Asamblea General Ordinaria</strong> legalmente convocada, la cual debe cumplir con el quórum y los procedimientos requeridos para que este nombramiento se declare legalmente instalado.</p>
          <p>Una vez nombrado por la <strong>Asamblea General</strong> el administrador acudirá a la oficina desconcentrada de la <strong>Procuraduría Social PROSOC </strong>que le corresponda en un plazo no mayor de 15 días hábiles a efecto de darse de alta y deberá cumplir con los requisitos establecidos por la <strong>Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal</strong> (LPCIDF) y por su Reglamento, así como por lo establecido en la <strong>Ley de la Procuraduría Social y su Reglamento</strong> la oficina emitirá la constancia de administrador dentro de los 15 días posteriores como lo estipula la ley.</p>
          <div id=\'attachment_748\' style=\'width: 310px\' class=\'wp-caption alignnone\'><img src=\'blog/administradoradeconsorcios-300x118.jpg\'/><p id=\'caption-attachment-748\' class=\'wp-caption-text\'>ADMINISTRADORA DE CONSORCIOS</p></div>
          <h2><strong>REGISTRO DE LA EMPRESA ADMINISTRADORA DE CONSORCIOS</strong></h2>
          <p>Para la empresa <strong>administradora de consorcios</strong> profesional en la Ciudad de México, es necesario contar con la certificación ante la <strong><a href=\'https://www.prosoc.com.mx\'>Procuraduría Social PROSOC</a>. </strong></p>
          <p>La administradora de consorcios también deberá contar con el registro de <strong>administrador condominal</strong> expedido por la <strong>Procuraduría Social PROSOC,</strong> este registro le otorga a una persona física o moral, personalidad jurídica frente a terceros para representar y administrar los <strong>bienes comunes de un consorcio</strong>.</p>
          <h2><strong>¿ CUÁNTO DURA EN SU ENCARGO UN ADMINISTRADOR DE CONSORCIOS ?</strong></h2>
          <p>Un <strong>administrador condominal ó profesional</strong> durará en su encargo un año, siempre que a consideración de la Asamblea haya cumplido en sus términos. En caso de ser <strong>administrador condominal</strong> podrá ser reelecto por dos periodos consecutivos más y posteriormente en otros periodos no consecutivos. Para el caso del <strong>administrador profesional</strong>, podrá ser ratificado siempre y cuando se renueve la fianza y el contrato correspondiente.</p>
          <h2><strong>FUNCIONES DE LA ADMINISTRADORA DE EDIFICIOS Y CONSORCIOS</strong></h2>
          <p>Una <strong>empresa administradora de consorcios</strong> debe llevar un libro de actas autorizado por la <strong>Asamblea</strong> y como entre sus funciones está la de vigilar <strong>los bienes del consorcio</strong> y los servicios comunes, así como promover la integración, organización y el desarrollo de la comunidad.</p>
          <p>La <strong>empresa administradora de consorcios</strong> deberá promover los cuatro comités que establece la ley LPCIDF además de representar las decisiones tomadas por la asamblea, que es la máxima <strong>autoridad en el consorcio</strong>, Así mismo, entre sus funciones está la de entregar mensualmente a cada consorcista, un estado de cuenta del consorcio, con el visto bueno del <strong>Comité de Vigilancia del consorcio</strong> que contemple, entre otros aspectos: los ingresos y egresos del mes anterior, el saldo de las cuentas bancarias, la relación detallada de las cuotas por pagar a proveedores, así como la relación de morosos y montos de deuda.</p>
          <p>La empresa administradora de consorcios, tiene por ley que dirimir las controversias derivadas de actos de molestia entre consorcistas poseedores o habitantes en general, para mantener la paz y la tranquilidad entre los mismos.</p>
          <p>Con siete días de anticipación al vencimiento de su contrato, debe convocar a una asamblea a efecto de entregar o que la misma le ratifique en su nombramiento.</p>
          <p>Los administradores deberán también fomentar la creación de cuatro Comités en los consorcios que administren:</p>
          <p>1) Comité de Ecología o Medio Ambiente.</p>
          <p>2) Comité Socio-Cultural.</p>
          <p>3) Comité de Seguridad y Protección Civil.</p>
          <p>4) Comité del Deporte.</p>
          <h2><strong>CULTURA CONDOMINAL EN MÉXICO </strong></h2>
          <p>Las administradoras y los administradores deberán ser promotores de la cultura condominal en los consorcios que administren, y mediadores entre los conflictos suscitados entre los propios consorcistas a fin de salvaguardar una sana convivencia condominal.</p>
          <h2><strong>OBLIGACIONES DEL ADMINISTRADOR DE CONSORCIOS </strong></h2>
          <p>En México el <strong>Administrador</strong> deberá cumplir con las obligaciones que señala el artículo 43 de la Ley de Propiedad en Condominio de inmuebles para el Distrito Federal, con la ejecución de los acuerdos que señale la <strong>Asamblea General de Condóminos</strong>, tomar los cursos impartidos por ésta H. Procuraduría Social, y tratándose de <strong>Administradores Profesionales</strong>, deberá CERTIFICARSE anualmente.</p>
          <h2><strong>RESPONSABILIDADES DE UN ADMINISTRADOR DE CONSORCIOS</strong></h2>
          <p>Las <strong>obligaciones de un administrador de consorcios</strong> si no se cumplen se pueden aplicar sanciones.</p>
          <h3>El no registrar su nombramiento ante la Procuraduría Social.</h3>
          <p>Multa de 46 a 271 unidades de cuenta de la ciudad de México de conformidad con los artículos 43 fracción XXVIII y 87 de la <strong>Ley de Propiedad en Condominio</strong> de Inmuebles para el Distrito Federal</p>
          <h3>El no registrar ante la <strong>Procuraduría Social</strong> el libro de actas.</h3>
          <p>Multa de 9 a 90 unidades de cuenta de la ciudad de México de conformidad con los artículos 43 fracción I y 87 de la <strong>Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal.</strong></p>
          <h3>El no convocar a Asambleas</h3>
          <p>Multa de 46 a 181 unidades de cuenta de la ciudad de México de conformidad con los artículos 32 y 87 de <strong>la Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal</strong></p>
          <h3>El no entregar estados de cuenta.</h3>
          <p>Multa de 46 a 271 unidades de cuenta de la ciudad de México de conformidad con los artículos 87 de la <strong>Ley de Propiedad en Condominio</strong> de Inmuebles para el Distrito Federal, y de configurarse el delito de <strong>Administración Fraudulenta</strong> el 234 del Código Penal para el Distrito Federal.</p>
          <h3>El no presentar el <strong>libro de actas en las Asambleas</strong>.</h3>
          <p>Multa de 46 a 181 unidades de cuenta dela ciudad de México de conformidad con el artículo 87 de la <strong>Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal.</strong></p>
          <h3>El no servir a los consorcistas difundiendo y ejecutando los acuerdos.</h3>
          <p>Multa de 46 a 181 unidades de cuenta de la ciudad de México de conformidad con el artículo 87 de la <strong>Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal.</strong></p>
          <h3>El no iniciar ante las instancias competentes los procedimientos administrativos.</h3>
          <p>Multa de 9 a 181 unidades de cuenta de la ciudad de México de conformidad con el artículo 87 de la <strong>Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal</strong></p>
          <h3>El no entregar a la administración entrante.</h3>
          <p>Multa de 46 a 271 unidades de cuenta dela ciudad de México de conformidad con el artículo 87 de la <strong>Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal</strong>.</p>
          <h3>El no mostrar a la Asamblea General, Comité de Vigilancia, y autoridad que así lo solicite, documentación referente a <strong>la Administración del consorcio</strong>.</h3>
          <p>Multa de 46 a 181 unidades de cuenta de la ciudad de México de conformidad con el artículo 87 de la <strong>Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal.</strong></p>
          <h3>El no expedir recibo cuando se le realice algún pago.</h3>
          <p>Multa de 46 a 271 unidades de cuenta de la ciudad de México de conformidad con el artículo 87 de la <strong>Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal.</strong> Asimismo, alterar las cuentas es un delito que se castiga con prisión, de conformidad con lo dispuesto en el artículo 234 del Código Penal para el Distrito Federal.</p>
          <h3>El no dirimir controversias derivadas de actos de molestia entre los consorcistas, poseedores o habitantes en general para mantener la paz y la tranquilidad entre los habitantes.</h3>
          <p>Multa de 9 a 90 unidades de cuenta de la ciudad de México  de conformidad con los artículos 43 fracción XXVI y 87 de la <strong>Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal.</strong></p>
          <h2><strong>CUANTO COBRA UN ADMINISTRADOR DE CONSORCIOS</strong></h2>
          <p>Aún siendo un administrador condominal se tiene todo el derecho de solicitar una remuneración económica por lo que implica el servicio de <strong>administración condominal</strong>.</p>
          <p>Esta remuneración es acordada por la asamblea general, para ambos <strong>tipos de administración de consorcios</strong>.  El costo de <strong>administración</strong> <strong>del edificio </strong>deberá de ser fijada tomando en cuenta la complejidad del consorcio, ejemplo: Número de unidades de propiedad privativa, áreas de uso común, nivel de servicio requerido, situación actual en términos legales. Etc.</p>
          <h2><strong>PLAN DE TRABAJO ADMINISTRADOR DE CONSORCIOS</strong></h2>
          <p>El plan de trabajo del administrador tiene que estar diseñado para atender los principales puntos de una administración.</p>
          <h3>Plan de trabajo en área Legal del consorcio</h3>
          <p>En la ciudad de México los consorcios y administraciones, deberán de estar registrados en la <strong>Procuraduría Social PROSOC</strong>, es importante que el plan de trabajo del administrador incluya primeramente un diagnóstico de cuál es la situación en términos legales del consorcista para identificar cuáles serían los alcances del proyecto en términos de trámites necesarios para estar en el marco legal.</p>
          <h3>Plan de trabajo para el área Financiera del consorcio</h3>
          <p>El plan para la <strong><a href=\'https://administraciondeconsorcios.com.mx/administracion-de-inmuebles/\'>administración de inmuebles</a> </strong>deberá incluir la <strong>situación financiera del consorcio</strong>, para identificar cuáles son los retos que se tienen en cuanto a niveles de morosidad.</p>
          <p>Una vez evaluado se podrá planificar cuales son las estrategias para reducir el nivel de <strong>morosidad en el consorcio</strong>.</p>
          <h3>Plan de trabajo para el área de <strong>Mantenimiento del consorcio</strong></h3>
          <p>El mantenimiento es uno de los rubros esenciales para el plan de trabajo condominal, ya que en base al plan de mantenimiento se podrán calcular las cuotas a pagar por cada consorcista.</p>
          <p>Es necesario contar con un buen plan de <strong>mantenimiento para el consorcista</strong>, esto para evitar caer en <strong>mantenimientos correctivos</strong> <strong>del consorcio</strong> que son más costosos y afectan financieramente al consorcio encareciendo las cuotas.</p>
          <h2><strong>REQUISITOS ADMINISTRADOR DE CONSORCIOS</strong></h2>
          <p>En la ciudad de México los requisitos para un <strong>administrador profesional de consorcios</strong> son estar registrado ante la <strong>Procuraduría Social de la Ciudad de México PROSOC, </strong>además de esto es requisito indispensable contar con conocimiento y experiencia que lo acredite como <strong>administrador profesional</strong>.</p>
          <h2><strong>CONTRATO PARA ADMINISTRADOR DE CONSORCIOS</strong></h2>
          <p>Los servicios de administración externa o <strong>administración profesional de consorcistas</strong> tienen que ser formalizados por medio de un contrato el cual deberá de estar registrado ante la <strong>Procuraduría Social de la Ciudad de México PROSOC, </strong>este contrato deberá no ser mayor a un año y recomendamos que este certificado por la <strong>PROFECO</strong></p>
          <h2><strong>PERFIL ADMINISTRADOR DE CONSORCIOS</strong></h2>
          <p>El <strong>administrador de consorcistas</strong>, debe ser un experto en temas legales, financieros y mantenimiento de inmuebles.</p>
          <p>Además de tener amplio conocimiento en estos tres rubros, es común que deba de tener un equipo especializado para soporte en cada tema.</p>
          <p>Deberá de ser un perfil que cuente con disponibilidad de horario ya que un buen servicio de <strong>administración</strong> <strong>condominal</strong> es el que da soporte a cualquier eventualidad que puedan tener sus clientes las 24 horas.</p>
          <p>Tiene que contar con la capacidad de ser un excelente mediador ante las situaciones de inconformidad que se presenten en consorcio.</p>
          <p>La facilidad de influenciar a los consorcistas para que exista una sana convivencia es de las principales características que debe de tener un buen <strong>administrador condominal.</strong></p>
          <p>Tolerancia a la frustración es necesaria, debido a que las decisiones tomadas en un <strong>consorcio</strong> son de común acuerdo y se necesita la presencia de la mayoría de los <strong>consorcistas</strong> para tomar decisiones.</p>
          <p>El <strong>administrador de consorcistas</strong> debe de tener la capacidad de ser proactivo para tratar de minimizar los posibles problemas que se pudieran presentar en el consorcista.</p>
          <h2><strong>ADMINISTRADOR PROFESIONAL DE CONSORCIOS</strong></h2>
          <p>Como ya hemos mencionado el <strong>administrador de consorcios profesional en la CDMX </strong>es aquel administrador que demuestra su capacidad y conocimiento para <strong>administrar consorcios</strong> mediante un examen ante la <strong>PROSOC Procuraduría Social de la Ciudad de México</strong>.</p>
          <h2><strong>CERTIFICACIÓN ADMINISTRADOR DE CONSORCIOS</strong></h2>
          <p>La certificación de un <strong>administrador de consorcistas</strong> es proporcionada por la <strong>PROSOC Procuraduría Social de la Ciudad de México</strong>. Esta se proporcionar mediante la acreditación de un examen que tiene un costo cercano a los $ 2,000.00.</p>
          <p>El examen se conforma de una serie preguntas derivadas de la Ley de propiedad en consorcio de inmuebles para el Distrito Federal.</p>
          <p>Anterior a presentar el examen es obligatorio tomar un curso presencial en las instalaciones de la <strong>PROSOC Procuraduría Social de la Ciudad de México, </strong>de tres días.</p>
          <p>Finalizando el curso se proporciona una folio que el participante lo acredita como administrador condominal.</p>
          <p>SI TE INTERESA CONOCER MAS DE NUESTRO SERVICIO VISITA <a href=\'https://www.administracionelgrove.com\'>ADMINISTRACIÓN DE CONSORCIOS</a></p>
          <p><strong>SI TE INTERESA UN PRESUPUESTO PARA TU consorcio, RECOMENDAMOS LLENAR EL SIGUIENTE FORMULARIO PARA PROPORCIONAR UNA <a href=\'https://administraciondeconsorcios.com.mx/cotizacion/\'>COTIZACIÓN DEL SERVICIO DE ADMINISTRACIÓN CONDOMINAL</a></strong></p>
                      </div>',
              'slug' => 'administradora-de-consorcios',
              'created_by' => NULL,
              'belongs_to' => NULL,
              'created_at' => NULL,
              'updated_at' => NULL,
            ),
            13 => 
            array (
              'id' => 13,
              'title' => 'ADMINISTRACIÓN DE INMUEBLES',
              'content' => '<div class=\'entry\'>
                        <h2>ADMINISTRACIÓN DE INMUEBLES</h2>
          <p>Administración de inmuebles en la ciudad de México, conoce la importancia de contratar un servicio profesional y el costo del servicio.</p>
          <h2>¿Que es la administración de inmuebles?</h2>
          <p>La administración de inmuebles es el proceso mediante el cual se planifica, dirige, controla, organiza y se optimiza los recursos de un inmueble, con el fin de garantizar que la seguridad, la salud, la organización, el cumplimiento de leyes y el mantenimiento de estructuras se cumplan en un nivel satisfactorio.</p>
          <p>Un servicio efectivo de administración de consorcistas, busca la sana convivencia de los consorcistas e incrementar la plusvalía de su patrimonio.</p>
          <h2 class=\'p1\'>¿ Es caro contratar a un experto en administración de inmuebles ?</h2>
          <p class=\'p1\'>Como empresa dedicada a la <strong>administración de inmuebles</strong>, hemos encontrado algunos de los<span class=\'Apple-converted-space\'>  </span>factores principales para la contratación de un especialista experto en ello, es cuando los <strong>consorcistas</strong> se encuentran descontentos por que buscan que la <strong>cuota de mantenimiento</strong> (la cual no ha sido analizada y establecida con base a la necesidad) alcance para todo, hasta para aquello de lo que cada uno está obligado de manera individual, así mismo hemos notado que la falta de estudio y de información con base a la normatividad es el factor que principalmente provoca esta falla la cual si no se llega a controlar puede generar disputas que generen un ambiente hostil entre los <strong>consorcistas</strong>.</p>
          <p class=\'p1\'>Por lo anterior cuando los <strong>consorcistas</strong> en medio de todo ese sin fin de discrepancias empiezan a pensar en contratar a una empresa de <strong>administración de inmuebles profesional</strong>,  se preguntan ¿qué tan caro será ese servicio profesional?.</p>
          <div id=\'attachment_714\' style=\'width: 310px\' class=\'wp-caption alignnone\'><img src=\'blog/administracion-de-inmuebles-300x139.jpg\'/><p id=\'caption-attachment-714\' class=\'wp-caption-text\'>administración de inmuebles</p></div>
          <h2>¿ Por que contratar un experto en administración profesional de inmuebles ?</h2>
          <p class=\'p1\'>Los <strong>consorcistas</strong> siempre revisarán la manera de ahorrar dinero y muchos de ellos prefieren mantenerse sin un experto en <strong>administración de inmuebles</strong> que puede dar solución a las distintas situaciones de manera práctica y objetiva. Nuestra labor como <a href=\'https://www.prosoc.com.mx/empresa-certificada-por-la-prosoc/\'>empresa certificada por la PROSOC</a> es hacer conscientes a esos <strong>consorcistas</strong> de la importancia de invertir en un <strong>administrador profesional de inmuebles</strong> y hacerles notar la diferencia que existe entre gasto e inversión cuando está de por medio la <strong>plusvalía del consorcio</strong> (su patrimonio).</p>
          <h2>¿ Cuanto cuesta un servicio de administración profesional de inmuebles ?</h2>
          <p class=\'p1\'>Los <strong>honorarios de un administrador profesional</strong> <strong>de inmuebles</strong> se calculan dependiendo del tipo de <strong>consorcio</strong>, <strong>antigüedad del inmueble</strong>, el tipo de <strong>mantenimiento del consorcio</strong> que ha recibido, número de <strong>inmuebles</strong> que forman parte del <strong>consorcio</strong>, la <strong>morosidad</strong> de aquellos <strong>consorcistas</strong> que no han pagado su <strong>cuota de mantenimiento</strong>, gestión para la solución de problemas tanto en el <strong>consorcio</strong> como entre los mismos <strong>consorcistas</strong>, asesoría y/o trámites para que el <strong>consorcio</strong> esté bajo la debida normatividad.</p>
          <p class=\'p1\'>Es indispensable que como empresa dedicada a la <strong>administración de consorcios</strong> sepa específicamente las <strong>necesidades del consorcio</strong> con base a sus condiciones y problemas que enfrenta se establezcan los honorarios del <strong>administrador profesional de inmuebles</strong>.</p>
          <p class=\'p1\'>Con base a lo anterior es indispensable que al establecer los <strong>honorarios del administrador profesional</strong> se tomen en cuenta las posibilidades de los <strong>consorcistas</strong>, pero que a su vez avale una remuneración justa tomando en cuenta todas las responsabilidades y servicio que engloba la labor del <strong>administrador profesional</strong>.</p>
          <p class=\'p1\'>Cuando las <strong>empresas de administración de consorcios</strong> saben la labor que implica el desempeñarse en las diferentes vertientes que representa este servicio y lo comprueban poniéndolo en práctica en el <strong>consorcio</strong> que lo necesita, los <strong>consorcistas</strong> siempre buscarán la manera de poder realizar ciertas estrategias para poder llegar a la cifra que se les solicita la empresa <a href=\'https://administraciondeconsorcios.com.mx/administradora-de-consorcios/\'><strong>administradora de consorcios</strong></a> ya que como se mencionó anteriormente es una sana y necesaria inversión.</p>
          <p>Si necesitas mayor información para un servicio de <strong>administración de inmuebles</strong> puedes conocer más de nuestro servicio de <strong>administración profesional de consorcios</strong> en la siguiente liga:</p>
          <blockquote class=\'wp-embedded-content\' data-secret=\'2qyp3Btp8K\'><p><a href=\'https://www.administracionelgrove.com\'>Administración de Condominios</a></p></blockquote>
          
                      </div>',
              'slug' => 'administracion-de-inmuebles',
              'created_by' => NULL,
              'belongs_to' => NULL,
              'created_at' => NULL,
              'updated_at' => NULL,
            ),
            14 => 
            array (
              'id' => 14,
              'title' => '¿Cuáles son las facultades de la asamblea general de consorcistas?',
              'content' => '<div class=\'entry\'>
                        <h2>¿Cuáles son las facultades de la asamblea general de consorcistas en la Ciudad de México?</h2>
          <p>¿Cuáles son las facultades de la asamblea general de consorcistas? La Asamblea General de Condóminos, de conformidad con la Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal, tiene las siguientes facultades:</p>
          <p><img src=\'blog/cuales-son-las-facultades-de-la-asamblea-general-de-consorcistas1.jpg\'/></p>
          <ol>
          <li>Modificar la Escritura Constitutiva del Régimen de Propiedad en Condominio y aprobar o reformar el Reglamento Interno del Condominio.</li>
          <li>Establecer las cuotas a cargo de los consorcistas, determinando para ello el sistema o esquema de cobro que considere más adecuado y eficiente de acuerdo a las características del consorcio. Así como fijar las tasas moratorias que deben cubrir los consorcistas en caso de incumplimiento del pago de cuotas.</li>
          <li>Nombrar y remover al Comité de Vigilancia.</li>
          <li>Instruir al Comité de Vigilancia o a quien se designe para proceder ante las autoridades competentes cuando el Administrador infrinja la Ley, la Escritura Constitutiva del Régimen de Propiedad en Condominio, el Reglamento Interno del Condominio y demás disposiciones legales aplicables.</li>
          <li>Nombrar y remover libremente al Administrador, en los términos de la <a href=\'http://www.aldf.gob.mx/vivienda/media/01_ley_propiedad_consorcio_df-2.pdf\'>Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal</a>, de la Escritura Constitutiva del Régimen de Propiedad en Condominio y el Reglamento Interno del Condominio.</li>
          <li>Fijar la remuneración relativa al Administrador.</li>
          <li>Precisar las obligaciones y facultades del Administrador frente a terceros y las necesarias respecto de los consorcistas, de acuerdo a la Escritura Constitutiva del Régimen de Propiedad en Condominio y el Reglamento Interno del Condominio.</li>
          <li>Resolver sobre la clase y monto de la garantía que deba otorgar el Administrador respecto al fiel desempeño de su misión, y al manejo de los fondos a su cuidado.</li>
          <li>Examinar y, en su caso, aprobar los estados de cuenta que someta el Administrador a su consideración, así como el informe anual de actividades que rinda el Comité de Vigilancia.</li>
          <li>Adoptar las medidas conducentes sobre los asuntos de interés común que no se encuentren comprendidos dentro de las funciones conferidas al Administrador.</li>
          <li>Discutir y, en su caso, aprobar el presupuesto de gastos para el año siguiente.</li>
          <li>Las demás que le confieren la Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal, Escritura Constitutiva del Régimen de Propiedad en Condominio, el Reglamento Interno del Condominio y demás disposiciones aplicables.</li>
          </ol>
          <p><strong><img src=\'blog/cuales-son-las-facultades-de-la-asamblea-general-de-consorcistas.jpg\'/></strong></p>
          <h2><strong>Inasistencia a las Asambleas Generales de Condóminos</strong></h2>
          <p>La Ley del Régimen de Propiedad en Condominio de Inmuebles para el Distrito Federal no penaliza a los consorcistas que no asistan a las Asambleas, sin embargo, en algunos Reglamentos Internos sí se establecen medidas que aplican diversos tipos de sanciones a los ausentes.</p>
          <p>Tenga presente que los acuerdos tomados en las Asambleas son obligatorios para todos los consorcistas, incluyendo a los ausentes y disidentes.</p>
          <p>Por ello, en la medida que sus ocupaciones se lo permitan, acuda a las Asambleas y participe en los acuerdos de la misma. Sus recomendaciones, observaciones y sugerencias son valiosas y serán bien recibidas.</p>
          <p>SI TE INTERESA CONOCER MAS DE NUESTRO SERVICIO VISITA <a href=\'https://www.administracionelgrove.com\'>ADMINISTRACIÓN DE CONSORCIOS</a></p>
                      </div>',
              'slug' => 'cuales-son-las-facultades-de-la-asamblea-general-de-consorcistas',
              'created_by' => NULL,
              'belongs_to' => NULL,
              'created_at' => NULL,
              'updated_at' => NULL,
            ),
            15 => 
            array (
              'id' => 15,
              'title' => '¿Qué es el comité de vigilancia?',
              'content' => '<div class=\'entry\'>
                        <h2>¿Qué es el comité de vigilancia según la ley de propiedad en consorcio del DF?</h2>
          <p>¿Qué es el comité de vigilancia? Los consorcistas electos en Asamblea General de Condóminos como sus representantes legales, que está integrado por dos y hasta cinco miembros dependiendo del número de unidades de propiedad exclusiva del consorcio, designando de entre ellos a un presidente y de uno a cuatro vocales, mismos que actúan de manera colegiada.</p>
          <p>El nombramiento de los miembros del Comité de Vigilancia es por un año, desempeñándose en forma honorífica. Pudiendo reelegirse sólo a la mitad de sus miembros por un período consecutivo, excepto el presidente que en ningún caso podrá ser reelecto en período consecutivo.</p>
          <p><img src=\'blog/que-es-el-comite-de-vigilancia-300x168.jpg\'/></p>
          <p>Para ser miembro del Comité de Vigilancia, obligatoriamente se debe cumplir con el requisito de ser propietario de una unidad de propiedad exclusiva, o acreditar la representatividad legal de éste, y estar al corriente en el pago de sus cuotas de mantenimiento y administración y otras aportaciones a favor del consorcio.</p>
          <p> </p>
          <h2>Comité de Vigilancia funciones y obligaciones</h2>
          <ol>
          <li>Cerciorarse de que el Administrador cumpla con los acuerdos de la Asamblea General de Condóminos.</li>
          <li>Supervisar que el Administrador lleve a cabo el cumplimiento de sus funciones.</li>
          <li>Contratar y dar por terminados los servicios profesionales a que se refiere el Artículo 41° de la<a href=\'http://www.aldf.gob.mx/vivienda/media/01_ley_propiedad_consorcio_df-2.pdf\'> Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal</a>.</li>
          <li>En su caso, dar su conformidad para la realización de las obras a que se refiere el Artículo 28° Fracción I de la Ley Propiedad en Condominio de Inmuebles para el Distrito Federal.</li>
          <li>Verificar y dictaminar los estados de cuenta que debe rendir el Administrador ante la Asamblea General de Condóminos.</li>
          <li>Constatar y supervisar la inversión de los fondos.</li>
          <li>Dar cuenta a la Asamblea General de Condóminos de sus observaciones sobre la administración del consorcio.</li>
          <li>Coadyuvar con el Administrador en observaciones a los consorcistas sobre el cumplimiento de sus obligaciones.</li>
          <li>Convocar a Asamblea General de Condóminos, cuando los consorcistas lo hayan requerido al Administrador y éste no lo haga dentro de los tres días siguientes a la petición. Asimismo, cuando a su juicio sea necesario informar a la Asamblea General de Condóminos de irregularidades en que haya incurrido el Administrador, con notificación a éste para que comparezca ante la Asamblea relativa.</li>
          <li>Solicitar la presencia de un representante de la Procuraduría Social del Distrito Federal o de un fedatario público en los casos previstos en la Ley Propiedad en Condominio de Inmuebles para el Distrito Federal o en los que considere necesario.</li>
          <li>Las demás que se deriven de la Ley Propiedad en Condominio de Inmuebles para el Distrito Federal, de la aplicación de otras que impongan deberes a su cargo, así como de la Escritura Constitutiva del Régimen de Propiedad en Condominio y del Reglamento Interno del Condominio.</li>
          </ol>
          <p>SI TE INTERESA CONOCER MAS DE NUESTRO SERVICIO VISITA <a href=\'https://www.administracionelgrove.com\'>ADMINISTRACIÓN DE CONSORCIOS</a></p>
                      </div>',
              'slug' => 'que-es-el-comite-de-vigilancia',
              'created_by' => NULL,
              'belongs_to' => NULL,
              'created_at' => NULL,
              'updated_at' => NULL,
            ),
            16 => 
            array (
              'id' => 16,
              'title' => '¿Qué es un administrador de consorcios?',
              'content' => '<div class=\'entry\'>
                        <h2>¿Qué es un administrador de consorcios?</h2>
          <p>¿Qué es un administrador de consorcios? La persona física o moral que designa la Asamblea General de Condóminos en los términos de la Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal y el Reglamento Interno del Condominio.</p>
          <p>Su designación o nombramiento se da por el voto de la mayoría que represente el cincuenta y uno por ciento del valor del consorcio, y puede ser removido cuando la misma Asamblea General de Condóminos lo estime conveniente, salvo el caso del Administrador nombrado en la Escritura Constitutiva del Régimen de Propiedad en Condominio, quien debe desempeñar dicho cargo durante el primer año.</p>
          <p>El Administrador debe contar con experiencia en la administración de inmuebles y con amplia solvencia moral.</p>
          <p>La Asamblea General de Condóminos fija a mayoría de votos, y cada vez que lo juzgue conveniente, las remuneraciones ordinarias y extraordinarias del Administrador, así como su forma de pago.</p>
          <h2>EL consorcista como administrador del consorcio</h2>
          <p>El nombramiento de Administrador puede darse a un consorcista, el encargo es por un año y su reelección es posible sólo por un período consecutivo más. Posteriormente, puede ser electo en otros períodos no consecutivos.</p>
          <p>Cuando la Asamblea General de Condóminos decida contratar servicios profesionales externos, el Comité de Vigilancia debe celebrar el contrato correspondiente conforme a la ley aplicable, el cual no debe exceder de un año, pudiendo ser renovado mientras en Asamblea no se determine lo contrario.</p>
          <p>En cualquiera de los casos, el nombramiento del Administrador debe ser presentado para su registro en la <a href=\'https://www.prosoc.com.mx/\'>Procuraduría Social del Distrito Federal POSOC</a>, dentro de los tres días hábiles siguientes a su designación. La Procuraduría emite el registro en un término de diez días hábiles, el cual tiene plena validez frente a terceros y ante las autoridades correspondientes.</p>
          <p><img src=\'blog/que-es-un-administrador-de-consorcios.jpg\'/></p>
          <p>Las medidas que adopte y las disposiciones que emita el Administrador dentro de sus funciones y con base en la Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal y el Reglamento Interno del Condominio, son obligatorias para todos los consorcistas.</p>
          <h2>Responsabilidades y obligaciones del administrador profesional de consorcios</h2>
          <ul>
          <li>Llevar un libro de actas de asamblea, debidamente autorizado por la Procuraduría.</li>
          <li>Cuidar y vigilar los bienes del consorcio y los servicios comunes, promover la integración, organización y desarrollo de la comunidad.</li>
          <li>Recabar y conservar los libros y la documentación relacionada con el consorcio, mismos que en todo tiempo podrán ser consultados por los consorcistas.</li>
          <li>Atender la operación adecuada y eficiente de las instalaciones y servicios generales.</li>
          <li>Realizar todos los actos de administración y conservación que el consorcio requiera en sus áreas comunes; así como contratar el suministro de la energía eléctrica y otros bienes necesarios para los servicios, instalaciones y áreas comunes, dividiendo el importe del consumo de acuerdo a lo establecido en la Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal y el Reglamento Interno del Condominio.</li>
          <li>Realizar las obras necesarias en los términos de la Fracción I del Artículo 28° de la Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal.</li>
          <li>Ejecutar los acuerdos de la Asamblea, salvo en lo que ésta designe a otras personas para tal efecto.</li>
          <li>Recaudar de los consorcistas lo que a cada uno corresponda aportar para los fondos de mantenimiento y administración y el de reserva, así como el de las cuotas extraordinarias de acuerdo a los procedimientos y periodicidad establecidos por la Asamblea General de Condóminos.</li>
          <li>Efectuar los gastos de mantenimiento y administración del consorcio, con cargo al fondo correspondiente, en los términos del Reglamento Interno del Condominio.</li>
          <li>Otorgar recibo por cualquier pago o aportación que reciba a favor del consorcio.</li>
          <li>Entregar mensualmente a cada consorcista, recabando constancia de quien lo reciba, un estado de cuenta del consorcio que muestre:</li>
          <li>Relación pormenorizada de ingresos y egresos del mes anterior.</li>
          <li>Estado consolidado de las aportaciones y cuotas pendientes. El Administrador tendrá a disposición de los consorcistas, que lo soliciten, una relación pormenorizada de los mismos.</li>
          <li>Saldo y fines para los que se destinarán los fondos el mes siguiente.</li>
          <li>Saldo de las cuentas bancarias, de los recursos en inversiones, con mención de intereses.</li>
          <li>El consorcista tendrá un plazo de ocho días contados a partir de la entrega de dicha documentación para formular las observaciones u objeciones que considere pertinentes.</li>
          <li>Transcurrido dicho plazo se considerará que está de acuerdo con la misma, a reserva de la aprobación de la Asamblea, en los términos de la Ley de Propiedad en Condominio de Inmuebles del Distrito Federal.</li>
          <li>Convocar a Asamblea en los términos establecidos en la Ley de Propiedad en Condominio de Inmuebles del Distrito Federal y el Reglamento Interno del Condominio.</li>
          <li>Representar a los consorcistas para la contratación a terceros de los locales, espacios o instalaciones de propiedad común que sean objeto de arrendamiento, comodato o que se destinen al comercio, ajustándose a lo establecido por las leyes correspondientes y el Reglamento Interno del Condominio.</li>
          <li>Cuidar con la debida observancia de las disposiciones de la Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal, el cumplimiento del Reglamento Interno del Condominio y de la Escritura Constitutiva del Régimen de Propiedad En Condominio.</li>
          <li>Exigir, con la representación de los demás consorcistas, el cumplimiento de las disposiciones de la Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal y el Reglamento Interno del Condominio. Solicitando en su caso el apoyo de la autoridad que corresponda.</li>
          <li>En relación con los bienes comunes del consorcio, el Administrador tendrá facultades generales para pleitos, cobranzas y actos de administración de bienes, incluyendo a aquellas que requieran cláusula especial conforme a la Ley.</li>
          <li>Cumplir con las disposiciones dictadas por la Ley de Protección Civil y su Reglamento.</li>
          <li>Iniciar, previa autorización de la Asamblea General, los procedimientos administrativos o judiciales que procedan contra los consorcistas que incumplan de manera reiterada con sus obligaciones e incurran en violaciones a la Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal, de la Escritura Constitutiva del Régimen de Propiedad en Condominio y del Reglamento Interno del Condominio.</li>
          <li>Realizar las demás funciones y cumplir con las obligaciones que establezcan a su cargo la Escritura Constitutiva del Régimen de Propiedad en Condominio, el Reglamento Interno del Condominio, la Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal y demás disposiciones legales aplicables, solicitando, en su caso, el apoyo de la Procuraduría Social del Distrito Federal para su cumplimiento.</li>
          <li>Estudiar las necesidades particulares del edificio y con base en ellas elaborar anualmente un presupuesto de gastos mensuales y anuales que presentará para su estudio y aprobación o modificación por la Asamblea General de Condóminos. Este presupuesto deberá contener un análisis de los gastos con la designación precisa de los grupos de consorcistas entre los que se distribuirán éstos.</li>
          <li>Es de su exclusiva incumbencia y responsabilidad el nombramiento y remoción del personal de administración y servicio que estará a sus órdenes directas.</li>
          <li>En caso de siniestro parcial, recibirá la indemnización correspondiente que empleará exclusivamente para volver las cosas al estado que guardaban.</li>
          <li>Vigilar la ejecución de los trabajos necesarios que bajo la dirección de un Arquitecto se lleven a cabo en las partes de propiedad común, de cualquier categoría que estos sean.</li>
          <li>Sin necesidad de comunicarlo previamente a los diversos propietarios, el Administrador ordena las pequeñas reparaciones que la conservación del inmueble requiere.</li>
          <li>Cuando los trabajos sean de importancia y urgencia, el Administrador debe ordenarlos, pero dando aviso a todos los consorcistas al iniciarlos.</li>
          <li>Si los trabajos son de importancia, pero no de urgencia debe consultar a la Asamblea General de Condóminos.</li>
          <li>Cuando la Asamblea General de Condóminos designa una nueva administración, la saliente debe entregar, en un término que no exceda de siete días naturales al día de la designación, todos los documentos incluyendo los estados de cuenta, valores, archivos, muebles, equipos y demás bienes que tuvo bajo su resguardo y responsabilidad. La entrega sólo puede posponerse por resolución judicial. Debiéndose levantar, en todos los casos, acta circunstanciada de la misma.</li>
          </ul>
          <h2><strong>Desavenencias con el Administrador de consorcios</strong></h2>
          <p>La Procuraduría Social del Distrito Federal tiene diseñado un proceso de conciliación, que se fundamenta en la buena fe de las partes y el deseo de resolver de manera conjunta la problemática del consorcio. El trámite es muy fácil y sin costo alguno.</p>
          <p>Los Administradores o Comités de Vigilancia que a juicio de la Asamblea General, Consejo, o de la Procuraduría no hagan un buen manejo o vigilancia de las cuotas de servicios, mantenimiento y administración, de reserva o extraordinarias, por el abuso de su cargo o incumplimiento de sus funciones, o se ostenten como tal sin cumplir lo que esta Ley y su reglamento establecen para su designación, estarán sujetos a las sanciones establecidas en las fracciones I, II, III y IV de este artículo, aumentando un 50% la sanción que le corresponda, independientemente de las responsabilidades o sanciones a que haya lugar, contempladas en otras Leyes;</p>
          <p>SI TE INTERESA CONOCER MAS DE NUESTRO SERVICIO VISITA <a href=\'https://www.administracionelgrove.com\'>ADMINISTRACIÓN DE CONSORCIOS</a></p>
                      </div>',
              'slug' => 'que-es-un-administrador-de-consorcios',
              'created_by' => NULL,
              'belongs_to' => NULL,
              'created_at' => NULL,
              'updated_at' => NULL,
            ),
            17 => 
            array (
              'id' => 17,
              'title' => '¿Es obligatorio que un consorcista sea administrador?',
              'content' => '<div class=\'entry\'>
                        <h2>¿Es obligatorio que un consorcista sea administrador?</h2>
          <p>¿Es obligatorio que un consorcista sea administrador? La <a href=\'http://www.aldf.gob.mx/vivienda/media/01_ley_propiedad_consorcio_df-2.pdf\'>Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal</a> no establece que sea obligatorio ser administrador y por consecuencia no precisa sanción alguna para aquel consorcista que rechace el cargo.</p>
          <p>En el Distrito Federal los administradores pueden ser consorcistas, siempre y cuando cumplan con su registro ante la Procuraduría Social del Distrito Federal.</p>
          <p>Tiene que tomar un curso que normalmente se programa en las instalaciones de la procuraduría y una vez aprobado el examen contaran con un folio de registro.</p>
          <p>Este folio les permitira llevar su administración conforme al marco legal que solicita la Ley de Propiedad en Condominio del Distrito Federal.</p>
          <p>Sin embargo, los consorcistas en Asamblea General de Condóminos pueden adoptar otras determinaciones sobre el particular e inclusive, establecer disposiciones relativas en el Reglamento Interno del Condominio.</p>
          <p><img src=\'blog/es-obligatorio-que-un-consorcista-sea-administrador.jpg\'/></p>
          <p>SI TE INTERESA CONOCER MAS DE NUESTRO SERVICIO VISITA <a href=\'https://www.administracionelgrove.com\'>ADMINISTRACIÓN DE CONSORCIOS</a></p>
                      </div>',
              'slug' => 'es-obligatorio-que-un-consorcista-sea-administrador',
              'created_by' => NULL,
              'belongs_to' => NULL,
              'created_at' => NULL,
              'updated_at' => NULL,
            ),
            18 => 
            array (
              'id' => 18,
              'title' => '¿Qué son las cuotas de administración y mantenimiento?',
              'content' => '<div class=\'entry\'>
                        <h2>¿Qué son las cuotas de administración y mantenimiento?</h2>
          <p>¿Qué son las cuotas de administración y mantenimiento? De acuerdo a la <a href=\'http://www.aldf.gob.mx/vivienda/media/01_ley_propiedad_consorcio_df-2.pdf\'>Ley de Propiedad en Condominio de Inmuebles para el Distrito Federal</a>, las cuotas de administración y mantenimiento son las aportaciones a cargo de los consorcistas que se establecen para:</p>
          <p><img src=\'blog/que-son-las-cuotas-de-administracion-y-mantenimiento.jpeg\'/></p>
          <h3>Constituir el fondo de mantenimiento y administración</h3>
          <p>Estos fondos son destinados a cubrir el gasto corriente que se genere en la administración, operación y servicios no individualizados de las áreas comunes del consorcio. El importe de la cuota se establece distribuyendo los gastos entre el número de unidades de propiedad exclusiva, de acuerdo a su porcentaje de indiviso, y deben cubrirse conforme a la periodicidad y procedimiento estipulados en el Reglamento Interno del Condominio.</p>
          <h3>Constituir el fondo de reserva</h3>
          <p>El fondo de reserva es destinado a cubrir los gastos de adquisición de implementos y maquinarias con que debe contar el consorcio, obras, mantenimiento y reparaciones mayores. El importe de esta cuota también se establece en proporción al valor estipulado en la Escritura Constitutiva del Régimen de Propiedad en Condominio para cada unidad de propiedad exclusiva. El monto general y su periodicidad se establecen en el Reglamento Interno del Condominio.</p>
          <h3>Efectuar gastos extraordinarios</h3>
          <p>Se pueden efectuar gastos extraordinarios cuando el fondo de administración y mantenimiento no sea suficiente para cubrir un gasto corriente extraordinario. El importe de la cuota se establece, distribuyendo en partes iguales el importe del gasto entre todas las unidades de propiedad exclusiva.</p>
          <p>También se efectúan gastos extraordinarios cuando fondo de reserva no sea suficiente para cubrir la compra de algún implemento, realización de obras, mantenimiento y reparaciones mayores. El importe de la cuota se distribuirá conforme a lo establecido para el fondo de reserva.</p>
          <p>En tanto no se utilicen, los fondos deben invertirse en valores de inversión a la vista de mínimo riesgo, conservando la liquidez necesaria para solventar las obligaciones de corto plazo. El tipo de inversión debe ser autorizada por el Comité de Vigilancia.</p>
          <p>La Asamblea General de Condóminos determina anualmente el porcentaje de los frutos o utilidades obtenidas por las inversiones que deben aplicarse a cada uno de los fondos del consorcio.</p>
          <p>Así también, la Asamblea General de Condóminos es quien determina anualmente el porcentaje de los frutos o utilidades obtenidas por el arrendamiento de los bienes de uso común que deben aplicarse a cada uno de los fondos del consorcio.</p>
          <p>Para mayores informes del servicio profesional para <strong>administración de consorcios</strong> visita: <a href=\'https://www.administracionelgrove.com\'>ADMINISTRACIÓN DE CONSORCIOS</a></p>
                      </div>',
              'slug' => 'que-son-las-cuotas-de-administracion-y-mantenimiento',
              'created_by' => NULL,
              'belongs_to' => NULL,
              'created_at' => NULL,
              'updated_at' => NULL,
            ),
            19 => 
            array (
              'id' => 19,
              'title' => '¿Es obligatorio el pago de las cuotas de mantenimiento y administración?',
              'content' => '<div class=\'entry\'>
                        <h2>¿Es obligatorio el pago de las cuotas de mantenimiento y administración?</h2>
          <p>¿Es obligatorio el pago de las cuotas de mantenimiento y administración? Sí. Al acordar la Asamblea de Condóminos el importe de las cuotas, todos los consorcistas están obligados a pagar la que les corresponda de acuerdo al porcentaje de indiviso que represente su unidad de propiedad exclusiva.</p>
          <p> </p>
          <h2>¿Para que sirven el pago de las cuotas de mantenimiento y administración en un consorcio?</h2>
          <p>Las cuotas de mantenimiento y administración son utilizadas para efectuar pagos por: honorarios, sueldos y salarios de personal que trabaja en el consorcio; trabajos necesarios para mantener el consorcio en buen estado, que incluyen la operación cotidiana, reparación y conservación de las instalaciones y servicios generales; consumos de agua y energía eléctrica de áreas comunes, al no cubrirse en su totalidad y oportunamente las cuotas se pone en riesgo el funcionamiento seguro y eficaz del consorcio.</p>
          <p><img src=\'blog/es-obligatorio-el-pago-de-las-cuotas-de-mantenimiento-y-administracion-300x164.jpeg\'/></p>
          <p>Además, al no constituirse el fondo de reserva se carece de los recursos económicos necesarios para solventar reparaciones mayores, comprar equipos para reponer los existentes o invertir en nuevas instalaciones o equipos.</p>
          <p>Por consiguiente, le recomendamos que siempre pague con oportunidad las cuotas de administración y mantenimiento y del fondo de reserva que le correspondan.</p>
          <h2>Intereses por pago fuera de tiempo de las cuotas de mantenimiento y administración</h2>
          <p><span style=\'text-decoration: underline;\'><strong>IMPORTANTE:</strong></span> Las cuotas de administración y mantenimiento no están sujetas a compensación, excepciones personales ni ningún otro supuesto que pueda excusar su pago.</p>
          <p>Las cuotas de mantenimiento y administración, así como otras aportaciones a favor del consorcio, que se generen a cargo de cada unidad de propiedad exclusiva y que los consorcistas, sus arrendatarios o cesionarios del uso no cubran oportunamente en las fechas y bajo las formalidades establecidas causarán los intereses establecidos en el Reglamento Interno del Condominio.</p>
          <p>Lo anterior, independientemente de las sanciones a que se hagan acreedores por motivo de su incumplimiento en el pago y que se hayan determinado en el mismo Reglamento Interno del Condominio o en acuerdo en la Asamblea General de Condóminos.</p>
          <p>Además, el estado de liquidación de adeudos, intereses moratorios y pena convencional que estipule el Reglamento Interno del Condominio, si va suscrita por el Administrador y el Presidente del Comité de Vigilancia, acompañada de los correspondientes recibos pendientes de pago, así como de copia certificada por fedatario público o por la Procuraduría Social del Distrito Federal, del acta de Asamblea General relativa y del Reglamento en que se hayan determinado las cuotas a cargo de los consorcistas para los fondos de mantenimiento y administración y el de reserva, puede traer aparejada una acción en la vía ejecutiva civil. Esta acción podrá ejercerse cuando existan tres cuotas ordinarias o una extraordinaria pendiente de pago.</p>
          <h2>Suspensión de servicios por falta de pago de las cuotas de mantenimiento y administración</h2>
          <p><span style=\'text-decoration: underline;\'><strong>IMPORTANTE:</strong> </span>Cuando los servicios que se disfruten en áreas de su unidad de propiedad exclusiva y sean pagados con recursos de los fondos de mantenimiento y administración o el de reserva del consorcio, el Administrador podrá suspender los mismos al consorcista que no cumpla oportunamente con el pago de las cuotas de administración y mantenimiento o el de reserva, previa autorización del Comité de Vigilancia.</p>
          <p>Cuando se celebre un contrato traslativo de dominio en relación a una unidad de propiedad exclusiva, el Notario Público que elabore la escritura respectiva debe exigir a la parte vendedora una constancia de no adeudo, entre otros, del pago de las cuotas de administración y mantenimiento y el de reserva, debidamente firmada por el Administrador del consorcio.</p>
          <p>Recuerde que el Administrador del consorcio debe entregarle mensualmente un estado de cuenta individual y otro general del consorcio y usted dispone de un plazo de ocho días naturales para formular sus observaciones o inconformidades al respecto.</p>
          <p>Por incumplimiento en el pago oportuno de las cuotas ordinarias, extraordinarias de administración, de mantenimiento y las correspondientes al fondo de reserva, se aplicará multa de 10 a 100 días de salario mínimo general vigente en el Distrito Federal; más intereses moratorios y/o el embargo de bienes a través de un Juicio Ejecutivo Civil Art. 59 y 87 de la <a href=\'http://www.aldf.gob.mx/vivienda/media/01_ley_propiedad_consorcio_df-2.pdf\'>Ley de propiedad en Condominio de Inmuebles para el Distrito Federal</a>.</p>
          <p>SI TE INTERESA CONOCER MAS DE NUESTRO SERVICIO VISITA <a href=\'https://www.administracionelgrove.com\'>ADMINISTRACIÓN DE CONSORCIOS</a></p>
                      </div>',
              'slug' => 'es-obligatorio-el-pago-de-las-cuotas-de-mantenimiento-y-administracion',
              'created_by' => NULL,
              'belongs_to' => NULL,
              'created_at' => NULL,
              'updated_at' => NULL,
            ),
            20 => 
            array (
              'id' => 20,
              'title' => '¿Se permiten mascotas en el consorcio?',
              'content' => '<div class=\'entry\'>
                        <h2>¿Se permiten mascotas en el consorcio?</h2>
          <p>¿Se permiten mascotas en el consorcio? No está prohibida por ninguna ley la tenencia de mascotas dentro del consorcio.</p>
          <p>Sin embargo, es creciente el número de Reglamentos Internos y acuerdos de Asambleas Generales de Condóminos que regulan su tenencia y, en casos específicos, hasta las prohíben.</p>
          <p>Pero bajo ninguna circunstancia o justificación, los animales de naturaleza peligrosa, ruidosa, desagradable o nociva tendrán cabida dentro del consorcio.</p>
          <p>Para evitarse dificultades con sus vecinos, se sugiere que no contravenga el Reglamento Interno del Condominio o los acuerdos de la Asamblea General de Condóminos sobre la tenencia de mascotas.</p>
          <p><img src=\'blog/se-permiten-mascotas-en-el-consorcio-300x168.jpeg\'/></p>
          <h2>Las mascotas en el Reglamento interno del consorcio</h2>
          <p>Al respecto, es importante que considere y acate las medidas y limitaciones establecidas en el Reglamento Interno del Condominio sobre animales en las unidades de propiedad exclusiva o áreas comunes.</p>
          <p>Tome en cuenta que la Procuraduría Social del Distrito Federal <a href=\'https://www.prosoc.com.mx/\'>PROSOC</a> puede imponerle una multa por faltas que afecten la tranquilidad o la comodidad de la vida condominal.</p>
          <p>Se consideran faltas a la Ley de Justicia Cívica si su mascota transita dentro del consorcio sin que se hayan tomado las medidas de seguridad necesarias, defeca en las áreas comunes o ataca a otras personas o animales y por lo tanto el dueño es sancionado con una multa y según el caso, hasta arresto por setenta y dos horas.</p>
          <p><span style=\'text-decoration: underline;\'><strong>Por razones de higiene y seguridad, los consorcistas no deben hacer uso de los elevadores en compañía de sus mascotas.</strong></span></p>
          <p>Por favor, tómese un tiempo dentro de sus ocupaciones para informarse sobre el Régimen de Propiedad en Condominio en que usted ha invertido y/o vive.</p>
          <p>Artículo 21.- IX. Queda prohibido a los consorcistas, poseedores y en general a toda persona y habitantes del consorcio: Poseer animales que por su número, tamaño o naturaleza afecten las condiciones de seguridad, salubridad o comodidad del consorcio o de los consorcistas. En todos los casos, los consorcistas, poseedores, serán absolutamente responsables de las acciones de los animales que introduzcan al consorcio, observando lo dispuesto en la Ley de Protección de los Animales en el Distrito Federal; LEY DE PROPIEDAD EN consorcio DE INMUEBLES PARA EL DISTRITO FEDERAL.</p>
          <div class=\'post post_single post_single_business vc_row\'>
          <div class=\'post_ctn clearfix\'>
          <div class=\'entry\'>
          <p>SI TE INTERESA CONOCER MAS DE NUESTRO SERVICIO VISITA <a href=\'https://www.administracionelgrove.com\'>ADMINISTRACIÓN DE CONSORCIOS</a></p>
          </div>
          </div>
          </div>
          <div class=\'share_icons_business\'></div>
                      </div><div class=\'entry\'>
          <p>SI TE INTERESA CONOCER MAS DE NUESTRO SERVICIO VISITA <a href=\'https://www.administracionelgrove.com\'>ADMINISTRACIÓN DE CONSORCIOS</a></p>
          </div>',
              'slug' => 'se-permiten-mascotas-en-el-consorcio',
              'created_by' => NULL,
              'belongs_to' => NULL,
              'created_at' => NULL,
              'updated_at' => NULL,
            ),
            21 => 
            array (
              'id' => 21,
              'title' => 'Conservar áreas verdes',
              'content' => '<div class=\'entry\'>
                        <h2><strong>Conservar áreas verdes en áreas residenciales </strong></h2>
          <p>Conservar áreas verdes ; Derribar, trasplantar, podar, talar u ocasionar la muerte de una o más árboles, cambiar el uso o naturaleza de las áreas verdes, ni aun y por acuerdo que se haya establecido Te puede evitar una multa de 50 a 200 días de salario mínimo y/o de 3 a 9 años de prisión, Art. 87 Fracción II de la <a href=\'http://www.aldf.gob.mx/vivienda/media/01_ley_propiedad_consorcio_df-2.pdf\'>Ley en Propiedad en Condominio de Inmuebles del Distrito Federal</a> y Art. 343 Fracción IV del Código Penal del Distrito Federal.</p>
          <div class=\'post post_single post_single_business vc_row\'>
          <div class=\'post_ctn clearfix\'>
          <div class=\'entry\'>
          <div class=\'post post_single post_single_business vc_row\'>
          <div class=\'post_ctn clearfix\'>
          <div class=\'entry\'>
          <p><img src=\'blog/conservar-areas-verdes-1.jpeg\'/></p>
          <p>SI TE INTERESA CONOCER MAS DE NUESTRO SERVICIO VISITA <a href=\'https://www.administracionelgrove.com\'>ADMINISTRACIÓN DE CONSORCIOS</a></p>
          </div>
          </div>
          </div>
          <div class=\'share_icons_business\'></div>
          </div>
          </div>
          </div>
          <div class=\'share_icons_business\'></div>
                      </div><div class=\'entry\'>
          <div class=\'post post_single post_single_business vc_row\'>
          <div class=\'post_ctn clearfix\'>
          <div class=\'entry\'>
          <p><img src=\'blog/conservar-areas-verdes-1.jpeg\'/></p>
          <p>SI TE INTERESA CONOCER MAS DE NUESTRO SERVICIO VISITA <a href=\'https://www.administracionelgrove.com\'>ADMINISTRACIÓN DE CONSORCIOS</a></p>
          </div>
          </div>
          </div>
          <div class=\'share_icons_business\'></div>
          </div><div class=\'entry\'>
          <p><img src=\'blog/conservar-areas-verdes-1.jpeg\'/></p>
          <p>SI TE INTERESA CONOCER MAS DE NUESTRO SERVICIO VISITA <a href=\'https://www.administracionelgrove.com\'>ADMINISTRACIÓN DE CONSORCIOS</a></p>
          </div>',
              'slug' => 'conservar-areas-verdes',
              'created_by' => NULL,
              'belongs_to' => NULL,
              'created_at' => NULL,
              'updated_at' => NULL,
            ),
            22 => 
            array (
              'id' => 22,
              'title' => 'Ruido en el consorcio',
              'content' => '<div class=\'entry\'>
                        <h2>Ruido en el consorcio ocasionado por vecinos</h2>
          <p>Ruido en el consorcio Artículo 21.-Queda prohibido a los consorcistas, poseedores y en general a toda persona y habitantes del consorcio:</p>
          <p>II. Realizar acto alguno que afecte la tranquilidad de los demás consorcistas y/o poseedores, que comprometa la estabilidad, seguridad, salubridad y comodidad del consorcio, o incurrir en omisiones que produzcan los mismos resultados;</p>
          <h2>Ruido en el edificio ocasionado por obras</h2>
          <p>IV. En uso habitacional, realizar obras y reparaciones en horario nocturno, salvo los casos de fuerza mayor.</p>
          <p>Para este artículo se aplicará de manera supletoria la <a href=\'https://es.wikipedia.org/wiki/Ley_de_Cultura_C%C3%ADvica_del_Distrito_Federal\'>Ley de cultura Cívica del Distrito Federal</a> y demás leyes aplicables.</p>
          <div class=\'post post_single post_single_business vc_row\'>
          <div class=\'post_ctn clearfix\'>
          <div class=\'entry\'>
          <div class=\'post post_single post_single_business vc_row\'>
          <div class=\'post_ctn clearfix\'>
          <div class=\'entry\'>
          <div class=\'post post_single post_single_business vc_row\'>
          <div class=\'post_ctn clearfix\'>
          <div class=\'entry\'>
          <p><img src=\'blog/ruido-en-el-consorcio.jpeg\'/></p>
          <p>SI TE INTERESA CONOCER MAS DE NUESTRO SERVICIO VISITA <a href=\'https://www.administracionelgrove.com\'>ADMINISTRACIÓN DE CONSORCIOS</a></p>
          </div>
          </div>
          </div>
          </div>
          </div>
          </div>
          </div>
          </div>
          </div>
          <p> </p>
                      </div><div class=\'entry\'>
          <div class=\'post post_single post_single_business vc_row\'>
          <div class=\'post_ctn clearfix\'>
          <div class=\'entry\'>
          <div class=\'post post_single post_single_business vc_row\'>
          <div class=\'post_ctn clearfix\'>
          <div class=\'entry\'>
          <p><img src=\'blog/ruido-en-el-consorcio.jpeg\'/></p>
          <p>SI TE INTERESA CONOCER MAS DE NUESTRO SERVICIO VISITA <a href=\'https://www.administracionelgrove.com\'>ADMINISTRACIÓN DE CONSORCIOS</a></p>
          </div>
          </div>
          </div>
          </div>
          </div>
          </div>
          </div><div class=\'entry\'>
          <div class=\'post post_single post_single_business vc_row\'>
          <div class=\'post_ctn clearfix\'>
          <div class=\'entry\'>
          <p><img src=\'blog/ruido-en-el-consorcio.jpeg\'/></p>
          <p>SI TE INTERESA CONOCER MAS DE NUESTRO SERVICIO VISITA <a href=\'https://www.administracionelgrove.com\'>ADMINISTRACIÓN DE CONSORCIOS</a></p>
          </div>
          </div>
          </div>
          </div><div class=\'entry\'>
          <p><img src=\'blog/ruido-en-el-consorcio.jpeg\'/></p>
          <p>SI TE INTERESA CONOCER MAS DE NUESTRO SERVICIO VISITA <a href=\'https://www.administracionelgrove.com\'>ADMINISTRACIÓN DE CONSORCIOS</a></p>
          </div>',
              'slug' => 'ruido-en-el-consorcio',
              'created_by' => NULL,
              'belongs_to' => NULL,
              'created_at' => NULL,
              'updated_at' => NULL,
            ),
            23 => 
            array (
              'id' => 23,
              'title' => 'Separar basura en el consorcio',
              'content' => '<div class=\'entry\'>
                        <h2>Separar basura en CDMX</h2>
          <p>Separar basura en el consorcio. Los capitalinos ahora tendrán que dividir sus residuos en orgánicos, inorgánicos que ahora se dividen en reciclables, e inorgánicos no reciclables, y residuos voluminosos y de manejo especial.</p>
          <p>Artículo 26.- Son infracciones contra el entorno urbano de la Ciudad de México: Evita una multa de 10 a 150 días de salario mínimo y/o un arresto de hasta 24 horas., Ast. 68 y 69 de la Ley de Residuos Sólidos y Art. 26 Fracción IV de la Ley de Cultura Cívica, ambas del Distrito Federal.</p>
          <p> </p>
          <p><img src=\'blog/separar-basura-1-200x300.png\'/></p>
          <h2>Separar basura en el consorcio tipos de basura</h2>
          <p>De acuerdo con el gobierno capitalino los residuos que diariamente se producen en la ciudad se dividirán así con la <a href=\'http://data.sedema.cdmx.gob.mx/nadf24/index.html\'>nueva norma ambiental en la CDMX NADF-024</a>:</p>
          <h3>Orgánicos: restos de verdura, cáscaras de fruta, semillas, huesos, lácteos, sobrantes de comida, té, filtros, residuos de jardinería.</h3>
          <p>Separar adecuadamente tus residuos orgánicos permite que se puedan transformar en algo útil, por ejemplo: abono para las plantas, alimento para animales de granja, jabones y hasta en biogás, un combustible capaz de generar energía eléctrica a partir de materia orgánica en descomposición.</p>
          <h3>Inorgánicos reciclables: papel, cartón, plástico, metal, vidrios, envase.</h3>
          <p>El papel, cartón, vidrio, plásticos, metales, ropa etc; al separar basura en el consorcio pueden ser transformados en nuevos productos si no se contaminan con otros residuos</p>
          <p>Para sacarles provecho es necesario separarlos, y así garantizar que se puedan reincorporar al proceso de producción, y darles valor nuevamente.</p>
          <p>Por eso, entrégalos de manera separada al camión recolector. También puedes llevarlos al centro de acopio más cercano a tu domicilio. O bien, ¡llévalos al Mercado de Trueque e intercámbialos por productos agrícolas cultivados en nuestra ciudad!</p>
          <p>Todos los residuos que logran aprovecharse no llegan a rellenos sanitarios ni tiraderos a cielo abierto, por tanto, no contribuyen a las 12,843 toneladas de desechos que diariamente generamos en la CDMX.</p>
          <h3>Inorgánicos no reciclables: es decir, aquellos que no se puede reutilizar y sí es basura, como colillas de cigarro, envolturas metálicas, pañales, toallas sanitarias y papel higiénico.</h3>
          <p>Desafortunadamente son residuos que no se pueden reciclar no todos los residuos se pueden aprovechar debido al uso que se les ha dado, por lo que éstos terminarán en rellenos sanitarios o en tiraderos a cielo abierto.</p>
          <h3>Residuos voluminosos y de manejo especial: como televisores, refrigeradores, lavadoras, computadoras, celulares, muebles rotos. Estos serán aceptados por los recolectores de basura únicamente los domingos.</h3>
          <p>Al Separar basura en el consorcio Los residuos electrónicos, eléctricos y las pilas, son residuos de manejo especial, es decir, necesitan un tratamiento diferente, pues contienen metales pesados, y otros materiales muy tóxicos y contaminantes. Te invitamos a llevarlos al Reciclatrón para que sus componentes puedan ser aprovechados (plástico, vidrio, cobre, metales, tarjetas electrónicas, etc.).</p>
          <p>Estas acciones son parte del programa “Basura Cero”, un nuevo intento del gobierno local para tener un manejo responsable de las 13 toneladas que de desechos que se producen diariamente en la CDMX.</p>
          <p>SI TE INTERESA CONOCER MAS DE NUESTRO SERVICIO VISITA <a href=\'https://www.administracionelgrove.com\'>ADMINISTRACIÓN DE CONSORCIOS</a></p>
                      </div>',
              'slug' => 'separar-basura',
              'created_by' => NULL,
              'belongs_to' => NULL,
              'created_at' => NULL,
              'updated_at' => NULL,
            ),
            24 => 
            array (
              'id' => 24,
              'title' => 'Agresiones verbales',
              'content' => '<div class=\'entry\'>
                        <h2>Agresiones verbales entre vecinos del consorcio</h2>
          <p>Agresiones verbales en el consorcio.- Vivir en consorcio, tiene sus ventajas como ya lo hemos mencionado anteriormente;</p>
          <p>Pero en ocasiones la sana convivencia entre los consorcistas se llegue a dar, La PROSOC reporta que los principales problemas entre los consorcistas que levantan quejas es por Ruido, filtraciones, ocupación de áreas comunes y uso de cajones de estacionamientos que no corresponden.</p>
          <p>Sugerimos que antes de llegar a las agresiones verbales tengamos bien definido nuestro reglamento interno del consorcio y las inconformidades con los vecinos se sugiere que se lleven por medio de la administración en turno.</p>
          <p>Una de las ventajas de contar con un administrador externo es que es imparcial y objetivo en las soluciones a los problemas que puedan tener los consorcios.</p>
          <h2>TÍTULO TERCERO INFRACCIONES Y SANCIONES CAPÍTULO I INFRACCIONES Y SANCIONES</h2>
          <p>Artículo 23.- Son infracciones contra la dignidad de las personas;<br>
          I. Vejar o maltratar física o verbalmente a cualquier persona;<br>
          III. Propinar a una persona, en forma intencional y fuera de riña, golpes que no le causen lesión; y</p>
          <p>IV. Lesionar a una persona siempre y cuando las lesiones que se causen de acuerdo al dictamen médico tarden en sanar menos de quince días.</p>
          <p>En caso de que las lesiones tarden en sanar más de quince días el juez dejará a salvo los derechos del afectado para que éste los ejercite por la vía que estime procedente.</p>
          <p><a href=\'http://www.aldf.gob.mx/vivienda/\'><img src=\'blog/agresiones-verbales.jpeg\'/></a></p>
          <p>La infracción establecida en la fracción I se sancionará con multa por el equivalente de 1 a 10 días de salario mínimo o con arresto de 6 a 12 horas.</p>
          <p>Las infracciones establecidas en las fracciones II y III se sancionarán con multa por el equivalente de 11 a 20 días de salario mínimo o con arresto de 13 a 24 horas.</p>
          <p>La infracción establecida en la fracción IV, se sancionará con arresto de veinticinco a treinta y seis horas. Sólo procederá la conciliación cuando el probable infractor repare el daño. Las partes de común acuerdo fijarán el monto del daño.</p>
          <p>SI TE INTERESA CONOCER MAS DE NUESTRO SERVICIO VISITA <a href=\'https://www.administracionelgrove.com\'>ADMINISTRACIÓN DE CONSORCIOS</a></p>
                      </div>',
              'slug' => 'agresiones-verbales',
              'created_by' => NULL,
              'belongs_to' => NULL,
              'created_at' => NULL,
              'updated_at' => NULL,
            ),
            25 => 
            array (
              'id' => 25,
              'title' => '¿Cómo revisar mi consorcio después de un sismo?',
              'content' => '<div class=\'entry\'>
                        <h2>¿Cómo revisar tu propiedad  tras un sismo?</h2>
          <p>¿ Cómo revisar mi consorcio después de un sismo ? México se considera una zona altamente sísmica debido a su ubicación geográfica.</p>
          <p>Existen 5 placas tectónicas que se dividen en territorio mexicano: Placa Norteamericana, Placa del Pacifico, Placa del Caribe, Placa de Cocos y Rivera.</p>
          <p>El suelo de la Ciudad de México en algunas delegaciones es altamente riesgoso para para los edificios, ya que los movimientos sísmicos se magnifican en intensidad en zonas blandas.</p>
          <p>La alta población en la Ciudad de México ha originado la tendencia a vivir en consorcios y en ocasiones estos han sido construidos en zonas donde estas edificaciones son un riesgo latente.</p>
          <h2>¿ Cómo revisar mi consorcio después de un sismo ?</h2>
          <p>Ante todo, siempre es muy recomendable consultar a un ingeniero especializado en estructuras para que evalúe los posibles daños y el riesgo de la construcción, esta evaluación dependerá  del número de pisos y la estructura del inmueble.</p>
          <p>Aquí los puntos a verificar inicialmente: ¿ Cómo revisar mi consorcio después de un sismo ?</p>
          <h3>¿Que revisar en mi casa después de un sismo?</h3>
          <p>Después de un sismo, ubica dentro de tu edificio cuáles son los muros de carga, las trabes, las columnas, los castillos, los cerramientos y los muros divisorios. También identifica cuáles son las instalaciones de luz/electricidad (apagadores, contactos, focos); de agua (cisternas, tuberías, tomas de agua); sanitarias (tuberías de aguas negras, sanitarios, coladeras y registros) y sobre todo las de gas (estufa, calentador, cilindro o tanque estacionario).</p>
          <p>El siguiente paso es monitorear el estado del edificio. De acuerdo con Protección Civil, es necesario que revises si hay fisuras o fracturas en acabados o elementos estructurales. Las fisuras normalmente afectan a los acabados superficiales del edificio, por lo que no implican un gran riesgo. Las fracturas afectan el soporte de la vivienda, por lo que es necesario que se realicen las reparaciones lo más pronto posible.</p>
          <h3>¿ Cómo revisar mi consorcio después de un sismo con respecto a las instalaciones de gas ?</h3>
          <p>Debes revisar que los cilindros o tanques estacionarios no tengan golpes ni raspaduras, ya que esto provoca la corrosión del material con el que están hechos. Revisa las perillas de la estufa y del calentador, ya que una junta mal colocada podría causar una fuga. También procura poner un poco de agua con jabón en las junturas de las tuberías de gas y si observas que se forman burbujas, es necesario que reemplaces esa conexión.</p>
          <p>Verifica si las tuberías de agua no presentan alguna fuga, ya que el agua podría contaminar los tinacos y las cisternas. También verifica si no hay alguna sobrecarga en las instalaciones de luz y que no haya cables expuestos para que no haya peligro de descarga eléctrica.</p>
          <p>La Secretaría de Protección Civil de la Ciudad de México recomienda que al menos dos veces al año o inmediatamente después de un sismo, realices las correcciones correspondientes y le des mantenimiento a tu hogar. Entre las afectaciones que tu consorcio puede sufrir está el que la humedad, las fisuras y el desprendimiento de acabados podrían dañar la estructura del inmueble. Las tuberías y coladeras tapadas podrían ocasionar fugas y socavaciones en los cimientos y el suelo de tu casa.</p>
          <p>En último caso, si tu casa sufrió daños importantes, es importante que la evacues y que realices las reparaciones lo más pronto posible antes de regresar a habitarla, de acuerdo con la Secretaría de Protección Civil.</p>
          <div id=\'attachment_849\' style=\'width: 269px\' class=\'wp-caption alignnone\'><img src=\'blog/como-revisar-mi-consorcio-despues-de-un-sismo.jpg\'/><p id=\'caption-attachment-849\' class=\'wp-caption-text\'>Cómo revisar mi consorcio después de un sismo</p></div>
          <h2><strong>Por qué tiembla tanto en la ciudad de México</strong></h2>
          <p>La Ciudad de México es una de las zonas con mayor riesgo a nivel nacional, esto debido a las condiciones del suelo, esta ciudad fue construida sobre un lago con suelo inestable.</p>
          <h2><strong>Cuáles son las zonas más seguras en la Ciudad de México para los consorcios ante un terremoto </strong></h2>
          <p>Las delegaciones Magdalena Contreras, Álvaro Obregón y Cuajimalpa son delegaciones que se asentaron sobre el volcán de San Miguel. Estas áreas formadas en su suelo con roca volcánica son de bajo riesgo para daños ante un terremoto.</p>
          <p>Las delegaciones como Milpa Alta, Xochimilco y Tlalpan también se encuentran sobre derrames de lava que forman parte de la sierra de Chihinautzin por lo que el riesgo también es menor.</p>
          <p>Adicionales hay zonas con aun menor riego como Ajusco, Ciudad universitaria, Pedregal, Lomas de Chapultepec y colonia Polanco.</p>
          <h2><strong>Cuáles son las zonas de mayor riesgo para los consorcios ante un sismo</strong></h2>
          <div id=\'attachment_776\' style=\'width: 244px\' class=\'wp-caption alignnone\'><img src=\'blog/como-revisar-mi-consorcio-despues-de-un-sismo-234x300.jpg\'/><p id=\'caption-attachment-776\' class=\'wp-caption-text\'>Como revisar mi consorcio después de un sismo</p></div>
          <p>Las zonas donde fueron construidas sobre lagos son las áreas de mayor riesgo para los consorcios ante un terremoto.</p>
          <p>En la Ciudad de México las delegaciones que fueron construidas con este tipo de suelo son: Cuauhtémoc, Gustavo A. Madero, Venustiano Carranza, Iztapalapa, Iztacalco y Tlahuac.</p>
          <p>Dentro de estas delegaciones hay colonias que tienen  una un riesgo mayor como Centro Histórico, Roma Norte, Narvarte, Condesa, Juarez, Doctores, Tabacalera y Tlatelolco.</p>
          <p>VISITA EL SITIO DEL <a href=\'http://www.ssn.unam.mx/\'>SISMOLÓGICO NACIONAL</a> PARA MAYOR INFORMACIÓN REFERENTE A SISMOS</p>
          <p>SI TE INTERESA CONOCER MAS DE NUESTRO SERVICIO VISITA <a href=\'https://www.administracionelgrove.com\'>ADMINISTRACIÓN DE CONSORCIOS</a></p>
          <p> </p>
                      </div>',
              'slug' => 'como-revisar-mi-consorcio-despues-de-un-sismo',
              'created_by' => NULL,
              'belongs_to' => NULL,
              'created_at' => NULL,
              'updated_at' => NULL,
            ),
            26 => 
            array (
              'id' => 26,
              'title' => 'ADMINISTRACIÓN DE CONSORCIOS SOFTWARE',
              'content' => '<div class=\'entry\'>
                        <h2><strong>ADMINISTRACIÓN DE CONSORCIOS SOFTWARE</strong></h2>
          <p><strong>Administración de consorcios software</strong>, para los profesionales en la administración condominal es recomendable utilizar herramientas de administración que faciliten el trabajo de gestión y la comunicación con los consorcistas.</p>
          <p>Para la administración de consorcios proactiva es necesario contar con la información en todo momento.</p>
          <p>Utilizar un <strong>software de administración de consorcios</strong> te permitirá mejorar la comunicación con los residentes y la transparencia en el uso y manejo de los recursos con los que cuenta el consorcio.</p>
          <p>A continuación, listamos los <strong>softwares para la administración</strong> <strong>de consorcios</strong> más recomendados.</p>
          <h2><strong>CONDOVIVE ADMINISTRACIÓN DE CONSORCIOS SOFTWARE</strong></h2>
          <p>Para la <strong>administración de consorcios software</strong> <strong>CondoVive</strong> es una aplicación web que facilita toda tu administración, con reportes gráficos y sencillos, estados de cuenta y avisos generales.</p>
          <div id=\'attachment_785\' style=\'width: 310px\' class=\'wp-caption alignnone\'><img src=\'blog/administracion-de-consorcios-software-condovive-300x300.jpg\'/><p id=\'caption-attachment-785\' class=\'wp-caption-text\'>administración de consorcios software condovive</p></div>
          <p><strong>CondoVive</strong> funciona bajo el esquema SaaS (Software as a Service) el cual significa el software se ofrece como un servicio, esto implica que la información siempre es tuya y nosotros proveemos la infraestructura informática para ayudarte a manejarla a cambio de un costo mensual desde la ventana de tu navegador. Esto tiene varias ventajas.</p>
          <h3><strong>RESPALDOS DIARIOS</strong></h3>
          <p>Tu información es muy muy importante, por eso tomamos una fotografía de todos tus datos cada día. De esta manera si algún dato se corrompe podemos regresar toda tu cuenta a una fecha en particular.</p>
          <h3><strong>SOPORTE </strong></h3>
          <p>Sabemos lo difícil que puede ser aprender algo nuevo, por eso estamos a un click de distancia. Si necesitas ayuda podemos compartir tu pantalla, y resaltar elementos del programa mientras platicamos contigo.</p>
          <h3><strong>ACTUALIZACIONES CONSTANTES</strong></h3>
          <p>Tal vez la mejor parte de utilizar el software CONDOVIVE como un servicio son las mejoras constantes sin costo basadas en tu retroalimentación y la de muchos administradores más de todo Latinoamérica.</p>
          <h3><strong>MÚLTIPLES CONSORCIOS</strong></h3>
          <p>CondoVive está pensado para asociaciones pequeñas hasta empresas administradoras grandes. Permite manejar de 1 consorcio de 10 casas a múltiples consorcios con centenares de casas, todo desde una sola aplicación.</p>
          <h3><strong>ACCESO DIFERENCIADO RESIDENTES Y ADMINISTRACIÓN</strong></h3>
          <p>CondoVive ha sido diseñado para ser fácil de usar, por esto manejamos dos interfaces diferentes, el administrador ve todas las opciones de un programa contable de presupuestos, avisos y reportes, mientras que el residente o propietario ve una interfaz sencilla y diseñada específicamente para ellos.</p>
          <h3><strong>MIGRACIÓN INICIAL CON ARCHIVOS DE EXCEL</strong></h3>
          <p>Sabemos lo importante que es tu información y lo tedioso que puede ser el utilizar un programa nuevo, por eso CondoVive te facilita unos archivos de excel para no hacer una captura manual.</p>
          <h3><strong>¿QUÉ TE OFRECE CONDOVIVE?</strong></h3>
          <h4><strong>FACILIDAD DE USO</strong></h4>
          <p>CondoVive es sencillo para un consorcio pequeño y poderoso para la empresa administradora.</p>
          <h4><strong>SOPORTE </strong></h4>
          <p>Sabemos que lo difícil es en el día a día. Por eso estamos a un click de distancia para asesorarte.</p>
          <h4><strong>TRANSPARENCIA AUTOMÁTICA</strong></h4>
          <p>Consulta en tiempo real los avances en cobranza y balances de la administración.</p>
          <h4><strong>FACTURACIÓN</strong></h4>
          <p>Desde un solo lugar envía y almacena tus facturas y CFDIS los ingresos capturados.</p>
          <h4><strong>REPORTES</strong></h4>
          <p>Ingresos, egresos, morosos, conciliaciones, estados de resultados y comparaciones con presupuestos.</p>
          <h4><strong>Exportar información a excel</strong></h4>
          <p>Todos los reportes e información del sistema es exportable en excel para tu facilidad.</p>
          <h4><strong>ENVÍO DE AVISOS</strong></h4>
          <p>Envía por email y publica en el muro avisos para todos los residentes y propietarios fácilmente.</p>
          <h4><strong>GESTIONA TUS ÁREAS COMUNES</strong></h4>
          <p>Desde la cancha de tenis hasta la terraza podrás llevar la administración de tus áreas comunes.</p>
          <h4><strong>GESTIÓN DE INGRESOS Y EGRESOS</strong></h4>
          <p>Solo captura ingresos y egresos, con eso generarás todos los reportes necesarios para tu administración.</p>
          <h4><strong>ESTADOS DE CUENTA POR RESIDENTE</strong></h4>
          <p>Mantén de manera sencilla y organizada estados de cuenta e históricos de cada residente.</p>
          <h4><strong>PRESENTAR QUEJAS Y SUGERENCIAS</strong></h4>
          <p>¿Alguna inconformidad? Preséntala desde el sistema y recibirás seguimiento de tu solicitud.</p>
          <h4><strong>WHITE LABEL</strong></h4>
          <p>Tu logo y marca en toda la plataforma sin ningún costo extra. Ideal si eres una empresa administradora.</p>
          <h2><strong>CONDOMISOFT ADMINISTRACIÓN DE CONSORCIOS SOFTWARE</strong></h2>
          <p>Todo lo relacionado con la administración de tu consorcio en un sólo lugar. Para la administración de consorcios software condomisoft es una de las mejores opciones en el mercado.</p>
          <div id=\'attachment_786\' style=\'width: 310px\' class=\'wp-caption alignnone\'><img src=\'blog/administracion-de-consorcios-software-condomisoft-300x79.jpg\'/><p id=\'caption-attachment-786\' class=\'wp-caption-text\'>administración de consorcios software condomisoft</p></div>
          <p>No importa si eres un vecino que administra su propio consorcio o una gran firma de administración de propiedades.</p>
          <p><strong>CondomiSOFT</strong> es el sistema que tiene una solución para ti 3 Modalidades elige la tuya:</p>
          <h3><strong>ACCESO ÚNICAMENTE PARA ADMINISTRADORES DE CONSORCIOS. </strong></h3>
          <p>Ideal para que lleves la contabilidad de tu consorcio o residencial y aproveches los múltiples reportes. Puedes llevar una bitácora de lo que ocurre en tu consorcio y muchas cosas más.</p>
          <h3><strong>ACCESO AL SISTEMA PARA ADMINISTRADORES DE CONSORCIOS Y CONDÓMINOS.</strong></h3>
          <p>Puedes aprovechar toda la funcionalidad de CONDOMISOFT y compartirlo con los residentes del consorcio para proporcionar transparencia a tu a administración condominal.</p>
          <h3><strong>INTEGRACIÓN EN TU SITIO WEB</strong></h3>
          <p>Muestra CondomiSoft como parte de la infraestructura de tu residencial o empresa de administración. Si lo requieres, se puede adaptar a un sitio web gratuito en un subdominio de CONDOMISOFT.</p>
          <h3><strong>¿QUE OFRECE CONDOMISOFT?</strong></h3>
          <h4><strong>CLOUD COMPUTING</strong></h4>
          <p>Utiliza CondomiSOFT y da seguimiento a tu contabilidad desde cualquier lugar que tenga conexión a internet.</p>
          <h4><strong>MOBILE FRIENDLY</strong></h4>
          <p>Utiliza CondomiSOFT cómodamente desde tu celular o tableta ya que su estructura está optimizada para su uso en dispositivos móviles.</p>
          <h4><strong>HTTPS</strong></h4>
          <p>La comunicación con el servidor está encriptada, lo que mantiene sus datos seguros mientras viajan por la red.</p>
          <h4><strong>SEGURIDAD</strong></h4>
          <p>El código de la aplicación fue escrito poniendo la seguridad como prioridad. Además escaneamos frecuentemente el sistema con múltiples herramientas que nos permiten detectar y reparar posibles vulnerabilidades en el software.</p>
          <h4><strong>PRIVACIDAD</strong></h4>
          <p>CondomiSOFT no hace ningún uso de la información que nos proporcionas por lo que tus datos permanecen seguros.</p>
          <h4><strong>SOPORTE</strong></h4>
          <p>Si tienes dudas o requieres apoyo el equipo de CondomiSOFT está cerca para ayudarte.</p>
          <h4><strong>MEJORA CONTINUA</strong></h4>
          <p>CondomiSOFT se mantiene en un proceso de mejora continua por lo que nuevos detalles y funcionalidades se agregan periódicamente.</p>
          <h4><strong>EMPIEZA AHORA</strong></h4>
          <p>Un mes gratis. Sin riesgos ni compromisos. No se requiere tarjeta de crédito.</p>
          <h2><strong>VECINOS 360 ADMINISTRACIÓN DE CONSORCIOS SOFTWARE</strong></h2>
          <p>Vecinos 360 es un software de administración de edificios y consorcios que te ayudará a gestionar tu comunidad con más eficiencia, más transparencia y una mejor comunicación.</p>
          <div id=\'attachment_782\' style=\'width: 310px\' class=\'wp-caption alignnone\'><img src=\'blog/administracion-de-consorcios-software-vecinos-360-300x162.png\'/><p id=\'caption-attachment-782\' class=\'wp-caption-text\'>administración de consorcios software vecinos 360</p></div>
          <p>La Administración de consorcios software Vecinos360 es una solución integral que le permite a los administradores gestionar con más eficiencia su negocio.</p>
          <p>Vecinos 360 es una plataforma web y móvil enfocada en satisfacer las necesidades de propietarios y administradores de consorcios facilitando la comunicación y gestión de las mismas.</p>
          <p>Ganadora del concurso Startup Perú, de Innovate Perú y del concurso Start Tel Aviv.</p>
          <p>Somos una iniciativa de la empresa Una Solutions, la cual es una incubadora de lanzamientos de proyectos de innovadores de emprendimientos.</p>
          <h3><strong>AHORRA TIEMPO Y CRECE</strong></h3>
          <p>Nuestra solución te permitirá simplificar tu gestión, recortando dramáticamente tiempos de procesos y costos de operación. Vecinos360 le devuelve agilidad a tu negocio, para que puedas enfocarte en crecer.</p>
          <h3><strong>TODA LA INFORMACIÓN SOBRE TUS EDIFICIOS EN UN SOLO LUGAR</strong></h3>
          <p>Dile adiós a los cuadernos y fuentes varias de información. Vecinos360 te permite tener toda la información de las comunidades que administras en una sola plataforma, con reportes exportables y documentos imprimibles.</p>
          <h3><strong>PROYECTA UNA IMAGEN DE SOLIDEZ Y MODERNIDAD</strong></h3>
          <p>Vecinos360 te permite presentar cuentas claras presentadas en recibos de pago completos y emitidos con tu imagen de marca. Tus vecinos pueden visualizarnos en la aplicación móvil, sin que tú tengas que imprimirlos.</p>
          <h3><strong>UNA MEJOR COMUNICACIÓN</strong></h3>
          <p>Una comunicación oportuna reduce los conflictos en una comunidad. Vecinos360 te permite comunicar incidentes, trabajos de mantenimiento y otros temas críticos de manera rápida y directa a través de nuestra aplicación móvil.</p>
          <h3><strong>COMUNICACIÓN  </strong></h3>
          <p>Fluida comunicación entre los vecinos y administradores buscando mejorar su comunidad a través de la plataforma web y móvil.</p>
          <h3><strong>TRANSPARENCIA  </strong></h3>
          <p>Información detallada en línea sobre la gestión administrativa, financiera y mantenimiento del edificio por medio de una cuenta personal.</p>
          <h3><strong>CONTINUIDAD  </strong></h3>
          <p>Al adquirir la plataforma de vecinos tomarás control sobre la información histórica de tu comunidad.</p>
          <h2><strong>VIVOOK ADMINISTRACIÓN DE CONSORCIOS SOFTWARE</strong></h2>
          <p>La mejor solución web en Latinoamérica para administradores de consorcios y edificios.</p>
          <div id=\'attachment_783\' style=\'width: 170px\' class=\'wp-caption alignnone\'><a href=\'http://www.vivook.com\'><img src=\'blog/administracion-de-consorcios-software-vivook.png\'/></a><p id=\'caption-attachment-783\' class=\'wp-caption-text\'>administración de consorcios software vivook</p></div>
          <p>En 2 minutos crea el website privado de tu consorcio o edificio, listo ya para manejar cuotas, pagos, avisos, informes y todo lo que un administrador profesional necesita.</p>
          <h3><strong>WEBSITE PRIVADO DE TU EDIFICIO O RESIDENCIAL</strong></h3>
          <p>Tu edificio o residencial dispondrá inmediatamente de un sitio web privado personalizado, donde el administrador, los propietarios y los residentes puedan comunicarse, interactuar y llevar la administración financiera.</p>
          <h3><strong>SERVICIO ADICIONAL DE TU EMPRESA</strong></h3>
          <p>Si te dedicas a la administración de edificios o residenciales, puedes vincular el acceso de Vivook a tu página web. De esta manera tus clientes (los residentes) tendrán que ingresar su email y password, desde la página web de su empresa y no desde Vivook.</p>
          <h3><strong>LA INFORMACIÓN CLARA Y OPORTUNA. LAS 24 HORAS. DEL DÍA</strong></h3>
          <p>Con Vivook tus residentes pueden mantenerse al día de toda la información del edificio o residencial a través de blogs temáticos pre organizados.</p>
          <p>Manejo de información exclusiva para miembros del comité de vigilancia, comité de residentes o miembros del consejo.</p>
          <p>El consejo de administración o comité de vigilancia, cuenta con votaciones y un blog exclusivo. Donde toda la información es confidencial y únicamente los miembros del consejo pueden consultarla. Pueden tratar asuntos y tomar decisiones, sin tener que reunirse físicamente.</p>
          <h3><strong>RESERVACIÓN DE INSTALACIONES EN LÍNEA.</strong></h3>
          <p>Los residentes podrán reservar las instalaciones o áreas comunes del edificio o residencial. El administrador confirmará o rechazará las solicitudes vía Vivook.</p>
          <h3><strong> </strong><strong>SOLICITUDES AL ADMINISTRADOR LAS 24 HRS. </strong></h3>
          <p>Cada vez que un propietario o residente quiera solicitarle o preguntarle algo al administrador, puede hacerlo aquí
          . No es necesario llamarle por teléfono o buscarlo personalmente. El administrador desde aquí puede darle seguimiento a las solicitudes y los residentes pueden consultar aquellas que sean públicas y ver si están pendientes de resolver, resueltas o anuladas.</p>
          <h3><strong>RESIDENTES FORMAN PARTE EN LA TOMA DE DECISIONES</strong></h3>
          <p>Vivook permite al administrador organizar encuestas y votaciones entre los propietarios y/o vecinos. De esta manera la opinión de los residentes y la preferencia de la mayoría siempre son tomadas en cuenta.</p>
          <h3><strong>LA ADMINISTRACIÓN MÁS TRANSPARENTE QUE NUNCA</strong></h3>
          <p>Si el administrador así lo decide, el comité de vigilancia o incluso todos los vecinos, pueden tener acceso a la información financiera del edificio o residencial (Ingresos, egresos y anexos de egresos, como facturas, tickets, presupuestos, recibos, reporte de presupuesto, cuentas bancarias y cuentas financieras de fondos, etc).</p>
          <h3><strong>CADA RESIDENTE SABE CUÁNTO DEBE Y CUÁNTO HA PAGADO.</strong></h3>
          <p>Las 24 hrs del día los 365 días del año los residentes tienen acceso a su estado de cuenta. Además de que el administrador, puede enviar e imprimir desde Vivook avisos de cobro y avisos de pago, personalizados con la imagen corporativa del edificio o residencial.</p>
          <h3><strong>LLEVA EL REGISTRO DE TUS ACTIVIDADES DE COBRANZA</strong></h3>
          <p>Vivook cuenta con un sistema de seguimiento en el cual se pueden registrar todas las actividades de cobranza que se han realizado para cada una de las viviendas.</p>
          <h3><strong>CONTROLA LA COBRANZA DE MANERA SENCILLA</strong></h3>
          <p>Con Vivook sabrá exactamente el monto de las cuentas por cobrar que existen en tu residencial, ya que contarás con varios reportes como: Cuentas por cobrar, morosos, antigüedad de adeudos, entre otros.</p>
          <h3><strong>PROGRAMACIÓN DE LAS TAREAS DE ADMINISTRACIÓN</strong></h3>
          <p>Lleva una agenda de las tareas de administración, no importa si son recurrentes o solo se realizan una sola vez. Además, podrás programar recordatorios automáticos para los responsables de realizar cada tarea.</p>
          <h3><strong>AHORRO EN RECURSOS DEL EDIFICIO O RESIDENCIAL</strong></h3>
          <p>Lo importante no es cuánto cuesta, sino cuánto te ahorra en lo que gastas actualmente en fotocopias de avisos, en tiempo que invierte para poder repartirlos y en llamadas telefónicas.</p>
          <h3><strong>PRUEBA GRATIS</strong></h3>
          <p>Usa el 100% de la funcionalidad de Vivook durante un mes sin costo. Registra aquí su edificio o residencial. Es muy rápido y totalmente gratis.</p>
          <p>SI TE INTERESA CONOCER MAS DE NUESTRO SERVICIO VISITA <a href=\'https://www.administracionelgrove.com\'>ADMINISTRACIÓN DE CONSORCIOS</a></p>
                      </div>',
              'slug' => 'administracion-de-consorcios-software',
              'created_by' => NULL,
              'belongs_to' => NULL,
              'created_at' => NULL,
              'updated_at' => NULL,
            ),
            27 => 
            array (
              'id' => 27,
              'title' => 'ADMINISTRACIÓN DE CONSORCIOS DF',
              'content' => '<div class=\'entry\'>
                        <h2><strong>ADMINISTRACIÓN DE CONSORCIOS EN EL DF CDMX</strong></h2>
          <p>Administración de consorcios DF .- La ciudad de México es hoy la ciudad con mayor crecimeinto en el sector inmobiliario a nivel nacional por lo que le permite a esta ciudad ser una oportunidad de negocio para <strong>empresas administradoras de consorcios y edificios.</strong></p>
          <p>Aproximadamente son tres millones de personas en la <strong>Ciudad de México</strong> que viven en un régimen de propiedad en <strong>consorcio</strong>. Existen cerca de ocho mil unidades habitacionales que no se registran ante la <a href=\'https://www.prosoc.com.mx\'><strong>Procuraduría Social del Distrito Federal (PROSOC)</strong></a></p>
          <h3>PROSOC CIUDAD DE MÉXICO</h3>
          <p>La <strong>Procuraduría Social del Distrito Federal (PROSOC)</strong> informa que son cerca de seis mil inmuebles donde los residentes no conocen en que invierten sus aportaciones mensuales.</p>
          <p>Existen varias quejas ante la <strong>Procuraduría Social del Distrito Federal (PROSOC)</strong> debido al mal manejo de los recursos de por parte de los <strong>administradores</strong> en turno, en algunos casos se han reportado fraudes por parte de <strong>administradores</strong> que lucran con las cuotas de los residentes.</p>
          <h2><strong>ADMINISTRACIÓN DE CONSORCIOS EN CDMX</strong></h2>
          <p>Es por esto que es muy importante hacer un análisis de la empresa que se seleccione para llevar la <strong>administración en el consorcio</strong> y a continuación te proporcionamos algunos consejos.</p>
          <h2><strong><img src=\'blog/administradores-de-consorcios-en-el-la-plata.jpg\'/></strong></h2>
          <h2><strong>PERMISO VIGENTE PARA LA ADMINISTRACIÓN DE CONSORCIOS DF</strong></h2>
          <p>Es obligatorio que para prestar el servicio de <strong>administración de consorcios</strong>, los administradores cuenten el <strong>registro ante la Procuraduría Social del Distrito Federal (PROSOC).</strong></p>
          <p>Este registro tendrá que renovarse año con año y debe estar actualizado para el <strong>administrador en turno</strong>.</p>
          <p>Es obligatorio que el administrador cuente con una <strong>FIANZA </strong>que deje en garantía, esta fianza será de común acuerdo con la junta de asamblea.</p>
          <p>Es recomendable que el contrato con el cual se formalice la contratación del servicio de <strong>administración de consorcios</strong> se encuentre registrado ante la <strong>PROFECO.</strong></p>
          <h2><strong><img src=\'blog/administradores-de-consorcios-300x168.jpg\'/></strong></h2>
          <h2><strong>HISTORIAL DE SANCIONES DE </strong><strong>LA EMPRESA </strong><strong>DE ADMINISTRACIÓN CONSORCIOS EN DF</strong></h2>
          <p>Generalmente, las sanciones impuestas a los <strong>administradores</strong> por medio de la <strong>Procuraduría Social del Distrito Federal (PROSOC) </strong>se registran y pueden ser solicitadas por el contratante para verificar el historial de sanciones. Esto nos ayudara a tomar una decisión para la elección de la empresa de <strong>administración de consorcios.</strong></p>
          <h2><strong><img src=\'blog/consorcios-300x168.jpg\'/></strong></h2>
          <h2><strong>SERVICIOS QUE OFRECE LA EMPRESA </strong><strong>DE ADMINISTRACIÓN CONSORCIOS EN DF</strong></h2>
          <p>En cualquier momento, se puede necesitar una estrategia de <strong>administración</strong> ó acceder a otras modalidades de servicio, como son servicios de mantenimiento como impermeabilización, plomería, albañilería, acabados etc.</p>
          <p>Es recomendable que la empresa de su selección cuente con personal operativo para cualquier evento de urgencia que se pueda necesitar.</p>
          <h2><strong><img src=\'blog/administradores-.jpg\'/></strong></h2>
          <h2><strong>CAPACIDAD DE BRIDAR LOS SERVICIOS DE ADMINISTRACIÓN CONSORCIOS EN DF</strong></h2>
          <p>Una empresa de <strong>administración de consorcios</strong> df debe tener la capacidad y preparación en su personal para prestar servicios de consultorías legales, mantenimiento y asesoría financiera.</p>
          <p>En <strong>HABITIA</strong> todos nuestros <strong>administradores</strong> cuentan con la certificación de la <strong>Procuraduría Social del Distrito Federal (PROSOC). </strong>Además de contar con diversos cursos de formación como <strong>asesores inmobiliarios</strong>.<img src=\'blog/administracion-de-consorcios-la-plata-300x168.jpg\'/></p>
          <h2><strong>EXPERIENCIA  DE LA EMPRESA DE ADMINISTRACION DE CONSORCIOS EN DF</strong></h2>
          <p>La <strong>administración de consorcios</strong> es un servicio complejo donde hay estrategias que se diseñan para riesgos potenciales. Solo personas con experiencia y conocimiento en el tema pueden asegurar que el <strong>consorcio</strong> siempre se encuentre en condiciones óptimas.</p>
          <h3>Experiencia en en mercado inmobiliario</h3>
          <p>Por esta razón es tan importante que la empresa de <strong>administración de consorcios y</strong> <strong>edificios</strong> acredite trayectoria y experiencia en el mercado a través de diferentes contratos con <strong>consorcios</strong>.</p>
          <h2><strong><img src=\'blog/la-plata.jpg\'/></strong></h2>
          <h2><strong>ANALISIS DE LA EMPRESA DE ADMINISTRACIÓN DE CONSORCIOS</strong> <strong>DF</strong></h2>
          <p>Es determinante evaluar en la parte administrativa de la compañía los siguientes aspectos:</p>
          <h3>Estabilidad financiera</h3>
          <p>porqué dependiendo de la misma se brinda el mejor servicio frente a los niveles de situaciones imprevistas que encierra la actividad económica de los <strong>consorcios</strong>.</p>
          <h3>Estabilidad organizacional</h3>
          <p>Se requiere mantener una congruencia entre el tamaño del <strong>consorcio a administrar</strong> y el tamaño de la empresa de <strong>administración de consorcios en el DF.</strong></p>
          <h3>Cobertura de seguros para el consorcio</h3>
          <p>La implementación de una fianza como garantía del servicio es obligatoria para la <strong>administración de consorcios en el DF.</strong></p>
          <h3>Variante de servicios adicionales para el edificio</h3>
          <p>La <strong>administración de consorcios</strong> es compleja y se requiere una plantilla de especialistas en temas legales de mantenimiento, finanzas.</p>
          <p>La capacidad de reacción de la empresa de <strong>administración de consorcios</strong> debe ser oportuna en el <strong>consorcio</strong> y a la hora que los <strong>consorcistas</strong> lo necesiten.</p>
          <h3>Certificaciones</h3>
          <p>Para la<strong> administración de consorcios en DF</strong> es necesaria la certificación emitida por la <strong>Procuraduría Social del Distrito Federal (PROSOC).</strong></p>
          <h2><strong><img src=\'blog/consorcio.jpg\'/></strong></h2>
          <h2><strong>OPERACIÓN DE LA EMPRESA DE ADMINISTRACION DE CONSORCIOS EN DF</strong></h2>
          <p>Evalúe antes de tomar cualquier decisión en materia de <strong>administración de consorcios y edificios</strong> los siguientes aspectos desde el punto de vista operativo:</p>
          <p>Capacidad del departamento de <strong>mantenimiento y administración</strong> en cuanto a: formación, experiencia, conocimiento, profesionalismo y capacidad de reacción</p>
          <p>Equipamiento para dar respuesta operativa a los esquemas de <strong>mantenimiento</strong>.</p>
          <p>Selección y capacitación del personal operativo</p>
          <h2><strong>ORIENTACIÓN DE LOS PROCESOS AL CLIENTE </strong></h2>
          <p>Las empresas de <strong>administración de consorcios en el DF</strong> deben asegurar una completa orientación hacia los procesos de servicio al cliente, aspecto fundamental para prestar un servicio de excelencia de acuerdo a las expectativas de cada cliente.</p>
          <p> </p>
          <p>SI TE INTERESA CONOCER MAS DE NUESTRO SERVICIO VISITA <a href=\'https://www.administracionelgrove.com\'>ADMINISTRACIÓN DE CONSORCIOS</a></p>
          <p> </p>
                      </div>',
              'slug' => 'administracion-de-consorcios-la-plata',
              'created_by' => NULL,
              'belongs_to' => NULL,
              'created_at' => NULL,
              'updated_at' => NULL,
            ),
          );
          
        $u = DB::table('posts');

        foreach ($posts as $ix => $post){
            unset($posts[$ix]['id']);
            unset($posts[$ix]['belongs_to']);
            unset($posts[$ix]['created_at']);
            unset($posts[$ix]['updated_at']);
            unset($posts[$ix]['updated_by']);
            unset($posts[$ix]['created_by']);

            $posts[$ix]['content'] = strip_tags ($posts[$ix]['content'], '<p><span><br><b><strong><em><i><u><strike><s><del><img><hr><ol><ul><li><h2><h3><center>'); 

            $posts[$ix]['content'] = preg_replace('/(\>)\s*(\<)/m', '$1$2', $posts[$ix]['content']);
            
            $posts[$ix]['content'] = trim($posts[$ix]['content']); 
            
            //debug($posts[$ix]);
            $id = $u->create($posts[$ix]);

            Debug::dd($id);
            //break;
        }
        
        
    }
       
}