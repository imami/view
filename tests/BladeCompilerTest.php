<?php

use Mockery as m;
use Illuminate\View\Compilers\BladeCompiler;

class BladeCompilerTest extends PHPUnit_Framework_TestCase {

	public function tearDown()
	{
		m::close();
	}


	public function testIsExpiredReturnsTrueIfCompiledFileDoesntExist()
	{
		$compiler = new BladeCompiler($files = $this->getFiles(), __DIR__);
		$files->shouldReceive('exists')->once()->with(__DIR__.'/'.md5('foo'))->andReturn(false);
		$this->assertTrue($compiler->isExpired('foo'));
	}


	public function testIsExpiredReturnsTrueWhenModificationTimesWarrant()
	{
		$compiler = new BladeCompiler($files = $this->getFiles(), __DIR__);
		$files->shouldReceive('exists')->once()->with(__DIR__.'/'.md5('foo'))->andReturn(true);
		$files->shouldReceive('lastModified')->once()->with('foo')->andReturn(100);
		$files->shouldReceive('lastModified')->once()->with(__DIR__.'/'.md5('foo'))->andReturn(0);
		$this->assertTrue($compiler->isExpired('foo'));
	}


	public function testCompilePathIsProperlyCreated()
	{
		$compiler = new BladeCompiler($this->getFiles(), __DIR__);
		$this->assertEquals(__DIR__.'/'.md5('foo'), $compiler->getCompiledPath('foo'));
	}


	public function testEchosAreCompiled()
	{
		$compiler = new BladeCompiler($this->getFiles(), __DIR__);
		$this->assertEquals('<?php echo $name; ?>', $compiler->compileString('{{$name}}'));
	}


	public function testIfStatementsAreCompiled()
	{
		$compiler = new BladeCompiler($this->getFiles(), __DIR__);
		$string = '@if (name(foo(bar)))
breeze
@endif';
		$expected = '<?php if (name(foo(bar))): ?>
breeze
<?php endif; ?>';
		$this->assertEquals($expected, $compiler->compileString($string));
	}


	public function testElseStatementsAreCompiled()
	{
		$compiler = new BladeCompiler($this->getFiles(), __DIR__);
		$string = '@if (name(foo(bar)))
breeze
@else
boom
@endif';
		$expected = '<?php if (name(foo(bar))): ?>
breeze
<?php else: ?>
boom
<?php endif; ?>';
		$this->assertEquals($expected, $compiler->compileString($string));	
	}


	public function testElseIfStatementsAreCompiled()
	{
		$compiler = new BladeCompiler($this->getFiles(), __DIR__);
		$string = '@if (name(foo(bar)))
breeze
@elseif (boom(breeze))
boom
@endif';
		$expected = '<?php if (name(foo(bar))): ?>
breeze
<?php elseif (boom(breeze)): ?>
boom
<?php endif; ?>';
		$this->assertEquals($expected, $compiler->compileString($string));	
	}


	public function testUnlessStatementsAreCompiled()
	{
		$compiler = new BladeCompiler($this->getFiles(), __DIR__);
		$string = '@unless (name(foo(bar)))
breeze
@endunless';
		$expected = '<?php if ( ! (name(foo(bar)))): ?>
breeze
<?php endif; ?>';
		$this->assertEquals($expected, $compiler->compileString($string));	
	}


	public function testIncludesAreCompiled()
	{
		$compiler = new BladeCompiler($this->getFiles(), __DIR__);
		$this->assertEquals('<?php echo $__env->make(\'foo\', get_defined_vars()); ?>', $compiler->compileString('@include(\'foo\')'));
		$this->assertEquals('<?php echo $__env->make(name(foo), get_defined_vars()); ?>', $compiler->compileString('@include(name(foo))'));
	}


	public function testShowEachAreCompiled()
	{
		$compiler = new BladeCompiler($this->getFiles(), __DIR__);
		$this->assertEquals('<?php echo $__env->showEach(\'foo\', \'bar\'); ?>', $compiler->compileString('@each(\'foo\', \'bar\')'));
		$this->assertEquals('<?php echo $__env->showEach(name(foo)); ?>', $compiler->compileString('@each(name(foo))'));
	}


	public function testYieldsAreCompiled()
	{
		$compiler = new BladeCompiler($this->getFiles(), __DIR__);
		$this->assertEquals('<?php echo $__env->yield(\'foo\'); ?>', $compiler->compileString('@yield(\'foo\')'));
		$this->assertEquals('<?php echo $__env->yield(name(foo)); ?>', $compiler->compileString('@yield(name(foo))'));
	}


	public function testShowsAreCompiled()
	{
		$compiler = new BladeCompiler($this->getFiles(), __DIR__);
		$this->assertEquals('<?php echo $__env->yieldSection(); ?>', $compiler->compileString('@show'));
	}


	public function testSectionStartsAreCompiled()
	{
		$compiler = new BladeCompiler($this->getFiles(), __DIR__);
		$this->assertEquals('<?php $__env->startSection(\'foo\'); ?>', $compiler->compileString('@section(\'foo\')'));
		$this->assertEquals('<?php $__env->startSection(name(foo)); ?>', $compiler->compileString('@section(name(foo))'));
	}


	public function testStopSectionsAreCompiled()
	{
		$compiler = new BladeCompiler($this->getFiles(), __DIR__);
		$this->assertEquals('<?php $__env->stopSection(); ?>', $compiler->compileString('@stop'));
	}


	protected function getFiles()
	{
		return m::mock('Illuminate\Filesystem');
	}

}